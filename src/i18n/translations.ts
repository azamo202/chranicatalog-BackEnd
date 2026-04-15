export type Language = 'en' | 'ar' | 'ku';

export const languages: { code: Language; label: string; dir: 'ltr' | 'rtl' }[] = [
  { code: 'en', label: 'English', dir: 'ltr' },
  { code: 'ar', label: 'العربية', dir: 'rtl' },
  { code: 'ku', label: 'کوردی', dir: 'rtl' },
];

type TranslationKeys = {
  nav: { home: string; products: string; support: string; about: string; contact: string };
  hero: { title: string; subtitle: string; cta: string };
  home: { featured: string; newArrivals: string; categories: string; viewAll: string };
  products: { title: string; filters: string; brand: string; category: string; priceRange: string; noResults: string; clearFilters: string; search: string };
  product: { description: string; specs: string; features: string; inquire: string; whatsappMsg: string };
  support: { title: string; manuals: string; videos: string; centers: string; download: string };
  about: { title: string; history: string; vision: string; mission: string };
  contact: { title: string; phone: string; email: string; address: string; social: string };
  footer: { rights: string; quickLinks: string };
  states: { loading: string; error: string; retry: string; empty: string };
};

export const translations: Record<Language, TranslationKeys> = {
  en: {
    nav: { home: 'Home', products: 'Products', support: 'Support & Warranty', about: 'About Us', contact: 'Contact Us' },
    hero: { title: 'Premium Home Appliances', subtitle: 'Discover our exclusive collection of world-class home appliances designed for modern living.', cta: 'Browse Catalog' },
    home: { featured: 'Featured Products', newArrivals: 'New Arrivals', categories: 'Shop by Category', viewAll: 'View All' },
    products: { title: 'Product Catalog', filters: 'Filters', brand: 'Brand', category: 'Category', priceRange: 'Price Range', noResults: 'No products found matching your criteria.', clearFilters: 'Clear Filters', search: 'Search products...' },
    product: { description: 'Description', specs: 'Technical Specs', features: 'Features', inquire: 'Inquire via WhatsApp', whatsappMsg: 'Hello, I would like to inquire about the product:' },
    support: { title: 'Support & Warranty Center', manuals: 'User Manuals', videos: 'Video Tutorials', centers: 'Service Centers', download: 'Download PDF' },
    about: { title: 'About Us', history: 'Our History', vision: 'Our Vision', mission: 'Our Mission' },
    contact: { title: 'Contact Us', phone: 'Phone', email: 'Email', address: 'Address', social: 'Follow Us' },
    footer: { rights: '© 2024 Chrani. All rights reserved.', quickLinks: 'Quick Links' },
    states: { loading: 'Loading...', error: 'Something went wrong.', retry: 'Try Again', empty: 'No items to display.' },
  },
  ar: {
    nav: { home: 'الرئيسية', products: 'المنتجات', support: 'الدعم والضمان', about: 'من نحن', contact: 'اتصل بنا' },
    hero: { title: 'أجهزة منزلية فاخرة', subtitle: 'اكتشف مجموعتنا الحصرية من الأجهزة المنزلية العالمية المصممة للحياة العصرية.', cta: 'تصفح الكتالوج' },
    home: { featured: 'منتجات مميزة', newArrivals: 'وصل حديثاً', categories: 'تسوق حسب الفئة', viewAll: 'عرض الكل' },
    products: { title: 'كتالوج المنتجات', filters: 'الفلاتر', brand: 'العلامة التجارية', category: 'الفئة', priceRange: 'نطاق السعر', noResults: 'لم يتم العثور على منتجات مطابقة.', clearFilters: 'مسح الفلاتر', search: 'ابحث عن المنتجات...' },
    product: { description: 'الوصف', specs: 'المواصفات التقنية', features: 'المميزات', inquire: 'استفسر عبر واتساب', whatsappMsg: 'مرحباً، أود الاستفسار عن المنتج:' },
    support: { title: 'مركز الدعم والضمان', manuals: 'أدلة المستخدم', videos: 'فيديوهات تعليمية', centers: 'مراكز الخدمة', download: 'تحميل PDF' },
    about: { title: 'من نحن', history: 'تاريخنا', vision: 'رؤيتنا', mission: 'مهمتنا' },
    contact: { title: 'اتصل بنا', phone: 'الهاتف', email: 'البريد الإلكتروني', address: 'العنوان', social: 'تابعنا' },
    footer: { rights: '© ٢٠٢٤ شراني. جميع الحقوق محفوظة.', quickLinks: 'روابط سريعة' },
    states: { loading: 'جاري التحميل...', error: 'حدث خطأ ما.', retry: 'حاول مجدداً', empty: 'لا توجد عناصر للعرض.' },
  },
  ku: {
    nav: { home: 'سەرەتا', products: 'بەرهەمەکان', support: 'پشتگیری و گەرەنتی', about: 'دەربارەی ئێمە', contact: 'پەیوەندیمان پێوە بکە' },
    hero: { title: 'ئامێرە ماڵییە نایاب', subtitle: 'کۆکراوەکەی تایبەتمەندمان ببینە لە ئامێرە ماڵییە جیهانییەکان کە بۆ ژیانی هاوچەرخ دیزاین کراون.', cta: 'کاتالۆگەکە بگەڕێ' },
    home: { featured: 'بەرهەمە تایبەتەکان', newArrivals: 'نوێترینەکان', categories: 'بە پۆل کڕین', viewAll: 'هەموو ببینە' },
    products: { title: 'کاتالۆگی بەرهەمەکان', filters: 'فلتەرەکان', brand: 'براند', category: 'پۆل', priceRange: 'مەودای نرخ', noResults: 'هیچ بەرهەمێک نەدۆزرایەوە.', clearFilters: 'سڕینەوەی فلتەرەکان', search: 'گەڕان بۆ بەرهەم...' },
    product: { description: 'وەسف', specs: 'تایبەتمەندییە تەکنیکییەکان', features: 'تایبەتمەندییەکان', inquire: 'پرسیار لە ڕێگەی واتساپ', whatsappMsg: 'سڵاو، دەمەوێ پرسیار بکەم دەربارەی بەرهەم:' },
    support: { title: 'ناوەندی پشتگیری و گەرەنتی', manuals: 'ڕێنمایی بەکارهێنەر', videos: 'ڤیدیۆ فێرکارییەکان', centers: 'ناوەندەکانی خزمەتگوزاری', download: 'داگرتنی PDF' },
    about: { title: 'دەربارەی ئێمە', history: 'مێژووی ئێمە', vision: 'دیدی ئێمە', mission: 'ئەرکی ئێمە' },
    contact: { title: 'پەیوەندیمان پێوە بکە', phone: 'تەلەفۆن', email: 'ئیمەیڵ', address: 'ناونیشان', social: 'فۆڵۆمان بکە' },
    footer: { rights: '© ٢٠٢٤ شرانی. هەموو مافەکان پارێزراون.', quickLinks: 'لینکە خێراکان' },
    states: { loading: 'چاوەڕوان بە...', error: 'هەڵەیەک ڕوویدا.', retry: 'دووبارە هەوڵبدەوە', empty: 'هیچ شتێک نییە بۆ پیشاندان.' },
  },
};
