<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Trait ManagesSortOrder
 *
 * يوفر منطقاً موحداً وآمناً لإدارة حقل sort_order عبر جميع الكنترولرات.
 * يعتمد على DB::transaction + lockForUpdate لمنع التعارض في العمليات المتزامنة.
 *
 * الاستخدام:
 *  - استدعِ adjustSortOrder() داخل DB::transaction دائماً.
 *  - استدعِ reorderAfterDelete() داخل DB::transaction بعد الحذف.
 *  - استدعِ normalizeOrder() لإصلاح بيانات قديمة أو تالفة.
 */
trait ManagesSortOrder
{
    /**
     * احسب الترتيب الصحيح وأعد ترتيب العناصر المجاورة.
     *
     * يجب استدعاء هذه الدالة داخل DB::transaction.
     *
     * @param int|null   $modelId   معرّف العنصر عند التعديل، null عند الإنشاء
     * @param int|null   $newOrder  الترتيب المطلوب (null أو 0 = آخر مكان)
     * @param Builder    $query     الاستعلام الأساسي المحدد بنطاقه الصحيح (مثلاً: Product::where('category_id', $id))
     * @return int  الترتيب الفعلي المُعين
     */
    protected function adjustSortOrder(?int $modelId, ?int $newOrder, Builder $query): int
    {
        // قفل الصفوف لمنع التعارض في الطلبات المتزامنة
        $count = (clone $query)->lockForUpdate()->count();
        $max   = (clone $query)->max('sort_order') ?? 0;

        // إذا لم يُحدَّد ترتيب أو كان غير صالح
        if (empty($newOrder) || $newOrder <= 0) {
            if ($modelId !== null) {
                // تعديل بدون تغيير الترتيب: نحتفظ بالقيمة الحالية
                return (int)((clone $query)->where('id', $modelId)->value('sort_order') ?? ($max + 1));
            }
            // إنشاء جديد: نضيف في آخر مكان
            return $max + 1;
        }

        if ($modelId !== null) {
            // ─── تعديل عنصر موجود ───────────────────────────────────────────────
            $oldOrder    = (int)((clone $query)->where('id', $modelId)->value('sort_order') ?? 0);
            $maxPossible = max(1, $count); // لا يمكن أن يتجاوز عدد العناصر
            $newOrder    = min($newOrder, $maxPossible);

            if ($newOrder === $oldOrder) {
                return $newOrder; // لا تغيير مطلوب
            }

            if ($newOrder < $oldOrder) {
                // الانتقال للأعلى: ازح العناصر الوسيطة للأسفل (+1)
                (clone $query)
                    ->where('id', '!=', $modelId)
                    ->whereBetween('sort_order', [$newOrder, $oldOrder - 1])
                    ->increment('sort_order');
            } else {
                // الانتقال للأسفل: ازح العناصر الوسيطة للأعلى (-1)
                (clone $query)
                    ->where('id', '!=', $modelId)
                    ->whereBetween('sort_order', [$oldOrder + 1, $newOrder])
                    ->decrement('sort_order');
            }
        } else {
            // ─── إنشاء عنصر جديد ────────────────────────────────────────────────
            $maxPossible = $count + 1;           // الحد الأقصى = عدد العناصر + 1
            $newOrder    = min($newOrder, $maxPossible);

            // ازح كل العناصر في هذا الموقع وما بعده للأعلى (+1)
            (clone $query)
                ->where('sort_order', '>=', $newOrder)
                ->increment('sort_order');
        }

        return $newOrder;
    }

    /**
     * أعد ترتيب العناصر المتبقية بعد حذف عنصر (يُغلق الفجوة).
     *
     * يجب استدعاء هذه الدالة داخل DB::transaction.
     *
     * @param Builder $query        الاستعلام المحدد بنطاقه الصحيح
     * @param int     $deletedOrder الترتيب الذي كان للعنصر المحذوف
     */
    protected function reorderAfterDelete(Builder $query, int $deletedOrder): void
    {
        if ($deletedOrder > 0) {
            (clone $query)
                ->where('sort_order', '>', $deletedOrder)
                ->decrement('sort_order');
        }
    }

    /**
     * أعد التسلسل الكامل لجميع العناصر (يُصلح الفجوات والتكرار في البيانات).
     *
     * استخدمها لإصلاح بيانات قديمة أو تالفة.
     * تستدعي هذه الدالة DB::transaction داخلياً.
     *
     * @param Builder $query الاستعلام المحدد بنطاقه الصحيح
     */
    protected function normalizeOrder(Builder $query): void
    {
        DB::transaction(function () use ($query) {
            $items = (clone $query)
                ->orderBy('sort_order', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get(['id', 'sort_order']);

            $counter = 1;
            foreach ($items as $item) {
                if ((int) $item->sort_order !== $counter) {
                    (clone $query)
                        ->where('id', $item->id)
                        ->update(['sort_order' => $counter]);
                }
                $counter++;
            }
        });
    }
}
