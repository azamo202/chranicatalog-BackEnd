import React from 'react';
import { Link } from 'react-router-dom';
import { useLanguage } from '@/i18n/LanguageContext';
import { getFeaturedProducts, getNewArrivals, categories } from '@/data/mockData';
import ProductCard from '@/components/ProductCard';
import { motion } from 'framer-motion';
import { ArrowRight, Sparkles, Star, Shield } from 'lucide-react';
import { Button } from '@/components/ui/button';

const HomePage: React.FC = () => {
  const { t, lang } = useLanguage();
  const featured = getFeaturedProducts();
  const newArrivals = getNewArrivals();

  const getCategoryName = (cat: typeof categories[0]) => {
    if (lang === 'ar') return cat.nameAr;
    if (lang === 'ku') return cat.nameKu;
    return cat.name;
  };

  return (
    <>
      {/* Hero */}
      <section className="relative bg-foreground text-background overflow-hidden" aria-label="Hero">
        <div className="absolute inset-0 bg-gradient-to-br from-primary/20 to-transparent" />
        <div className="container mx-auto px-4 py-24 md:py-32 relative z-10">
          <motion.div
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.7 }}
            className="max-w-2xl"
          >
            <h1 className="font-heading text-4xl md:text-6xl font-extrabold leading-tight mb-6">
              {t.hero.title}
            </h1>
            <p className="text-lg md:text-xl opacity-80 mb-8 max-w-lg">
              {t.hero.subtitle}
            </p>
            <Button asChild size="lg" className="bg-primary text-primary-foreground hover:bg-primary/90 gap-2 text-base">
              <Link to="/products">
                {t.hero.cta}
                <ArrowRight className="h-4 w-4" />
              </Link>
            </Button>
          </motion.div>
        </div>
      </section>

      {/* Trust bar */}
      <section className="border-b bg-background">
        <div className="container mx-auto px-4 py-6 flex flex-wrap justify-center gap-8 md:gap-16 text-sm text-muted-foreground">
          <div className="flex items-center gap-2"><Shield className="h-4 w-4 text-primary" /> 10-Year Warranty</div>
          <div className="flex items-center gap-2"><Star className="h-4 w-4 text-primary" /> Premium Quality</div>
          <div className="flex items-center gap-2"><Sparkles className="h-4 w-4 text-primary" /> Latest Technology</div>
        </div>
      </section>

      {/* Categories */}
      <section className="container mx-auto px-4 py-16" aria-label="Categories">
        <div className="flex items-center justify-between mb-8">
          <h2 className="font-heading text-2xl md:text-3xl font-bold">{t.home.categories}</h2>
          <Link to="/products" className="text-primary text-sm font-medium hover:underline flex items-center gap-1">
            {t.home.viewAll} <ArrowRight className="h-3 w-3" />
          </Link>
        </div>
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
          {categories.map((cat, i) => (
            <motion.div
              key={cat.id}
              initial={{ opacity: 0, y: 20 }}
              whileInView={{ opacity: 1, y: 0 }}
              viewport={{ once: true }}
              transition={{ delay: i * 0.05 }}
            >
              <Link
                to={`/products?category=${encodeURIComponent(cat.name)}`}
                className="group relative aspect-square rounded-lg overflow-hidden bg-muted block"
              >
                <img src={cat.image} alt={getCategoryName(cat)} className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy" />
                <div className="absolute inset-0 bg-gradient-to-t from-foreground/70 to-transparent" />
                <span className="absolute bottom-3 start-3 text-background font-heading font-semibold text-sm">
                  {getCategoryName(cat)}
                </span>
              </Link>
            </motion.div>
          ))}
        </div>
      </section>

      {/* Featured */}
      <section className="bg-secondary/30" aria-label="Featured products">
        <div className="container mx-auto px-4 py-16">
          <div className="flex items-center justify-between mb-8">
            <h2 className="font-heading text-2xl md:text-3xl font-bold">{t.home.featured}</h2>
            <Link to="/products" className="text-primary text-sm font-medium hover:underline flex items-center gap-1">
              {t.home.viewAll} <ArrowRight className="h-3 w-3" />
            </Link>
          </div>
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            {featured.map(p => <ProductCard key={p.id} product={p} />)}
          </div>
        </div>
      </section>

      {/* New Arrivals */}
      <section className="container mx-auto px-4 py-16" aria-label="New arrivals">
        <div className="flex items-center justify-between mb-8">
          <h2 className="font-heading text-2xl md:text-3xl font-bold">{t.home.newArrivals}</h2>
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {newArrivals.map(p => <ProductCard key={p.id} product={p} />)}
        </div>
      </section>
    </>
  );
};

export default HomePage;
