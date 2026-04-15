import React, { useState, useMemo } from 'react';
import { useSearchParams } from 'react-router-dom';
import { useLanguage } from '@/i18n/LanguageContext';
import { getVisibleProducts, brands, categories } from '@/data/mockData';
import ProductCard from '@/components/ProductCard';
import { EmptyState } from '@/components/StateComponents';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Slider } from '@/components/ui/slider';
import { Button } from '@/components/ui/button';
import { Search, SlidersHorizontal, X } from 'lucide-react';

const ProductsPage: React.FC = () => {
  const { t } = useLanguage();
  const [searchParams, setSearchParams] = useSearchParams();
  const allProducts = getVisibleProducts();

  const [search, setSearch] = useState('');
  const [selectedBrands, setSelectedBrands] = useState<string[]>([]);
  const [selectedCategories, setSelectedCategories] = useState<string[]>(
    searchParams.get('category') ? [searchParams.get('category')!] : []
  );
  const [priceRange, setPriceRange] = useState<[number, number]>([0, 2000]);
  const [showFilters, setShowFilters] = useState(false);

  const filteredProducts = useMemo(() => {
    return allProducts.filter(p => {
      if (search && !p.name.toLowerCase().includes(search.toLowerCase())) return false;
      if (selectedBrands.length && !selectedBrands.includes(p.brand)) return false;
      if (selectedCategories.length && !selectedCategories.includes(p.category)) return false;
      if (p.price < priceRange[0] || p.price > priceRange[1]) return false;
      return true;
    });
  }, [search, selectedBrands, selectedCategories, priceRange, allProducts]);

  const toggleBrand = (b: string) => setSelectedBrands(prev => prev.includes(b) ? prev.filter(x => x !== b) : [...prev, b]);
  const toggleCategory = (c: string) => setSelectedCategories(prev => prev.includes(c) ? prev.filter(x => x !== c) : [...prev, c]);
  const clearFilters = () => { setSearch(''); setSelectedBrands([]); setSelectedCategories([]); setPriceRange([0, 2000]); setSearchParams({}); };

  const hasFilters = search || selectedBrands.length || selectedCategories.length || priceRange[0] > 0 || priceRange[1] < 2000;

  const FilterPanel = () => (
    <div className="space-y-6">
      <div>
        <Label className="font-heading font-semibold text-sm mb-3 block">{t.products.brand}</Label>
        <div className="space-y-2">
          {brands.map(b => (
            <label key={b.id} className="flex items-center gap-2 text-sm cursor-pointer">
              <Checkbox checked={selectedBrands.includes(b.name)} onCheckedChange={() => toggleBrand(b.name)} />
              {b.name}
            </label>
          ))}
        </div>
      </div>
      <div>
        <Label className="font-heading font-semibold text-sm mb-3 block">{t.products.category}</Label>
        <div className="space-y-2">
          {categories.map(c => (
            <label key={c.id} className="flex items-center gap-2 text-sm cursor-pointer">
              <Checkbox checked={selectedCategories.includes(c.name)} onCheckedChange={() => toggleCategory(c.name)} />
              {c.name}
            </label>
          ))}
        </div>
      </div>
      <div>
        <Label className="font-heading font-semibold text-sm mb-3 block">{t.products.priceRange}</Label>
        <Slider
          min={0} max={2000} step={50}
          value={priceRange}
          onValueChange={(v) => setPriceRange(v as [number, number])}
          className="mb-2"
        />
        <div className="flex justify-between text-xs text-muted-foreground">
          <span>${priceRange[0]}</span>
          <span>${priceRange[1]}</span>
        </div>
      </div>
      {hasFilters && (
        <Button variant="outline" size="sm" onClick={clearFilters} className="w-full gap-2">
          <X className="h-3 w-3" /> {t.products.clearFilters}
        </Button>
      )}
    </div>
  );

  return (
    <div className="container mx-auto px-4 py-10">
      <header className="mb-8">
        <h1 className="font-heading text-3xl md:text-4xl font-bold mb-2">{t.products.title}</h1>
        <p className="text-muted-foreground">{filteredProducts.length} products</p>
      </header>

      <div className="flex gap-4 mb-6">
        <div className="relative flex-1">
          <Search className="absolute start-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
          <Input
            placeholder={t.products.search}
            value={search}
            onChange={e => setSearch(e.target.value)}
            className="ps-10"
            aria-label="Search products"
          />
        </div>
        <Button variant="outline" className="lg:hidden gap-2" onClick={() => setShowFilters(!showFilters)}>
          <SlidersHorizontal className="h-4 w-4" /> {t.products.filters}
        </Button>
      </div>

      <div className="flex gap-8">
        {/* Desktop sidebar */}
        <aside className="hidden lg:block w-64 shrink-0" aria-label="Filters">
          <FilterPanel />
        </aside>

        {/* Mobile filters */}
        {showFilters && (
          <div className="lg:hidden fixed inset-0 z-50 bg-background p-6 overflow-y-auto">
            <div className="flex justify-between items-center mb-6">
              <h2 className="font-heading font-bold text-lg">{t.products.filters}</h2>
              <Button variant="ghost" size="icon" onClick={() => setShowFilters(false)}>
                <X className="h-5 w-5" />
              </Button>
            </div>
            <FilterPanel />
          </div>
        )}

        {/* Products grid */}
        <div className="flex-1">
          {filteredProducts.length > 0 ? (
            <div className="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
              {filteredProducts.map(p => <ProductCard key={p.id} product={p} />)}
            </div>
          ) : (
            <EmptyState message={t.products.noResults} />
          )}
        </div>
      </div>
    </div>
  );
};

export default ProductsPage;
