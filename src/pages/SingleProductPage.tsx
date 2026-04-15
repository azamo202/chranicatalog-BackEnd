import React, { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useLanguage } from '@/i18n/LanguageContext';
import { getVisibleProducts } from '@/data/mockData';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { MessageCircle, ArrowLeft, Check } from 'lucide-react';

const SingleProductPage: React.FC = () => {
  const { t } = useLanguage();
  const { id } = useParams<{ id: string }>();
  const product = getVisibleProducts().find(p => p.id === id);
  const [selectedImage, setSelectedImage] = useState(0);

  if (!product) {
    return (
      <div className="container mx-auto px-4 py-20 text-center">
        <p className="text-lg text-muted-foreground">Product not found.</p>
        <Button asChild variant="outline" className="mt-4">
          <Link to="/products">
            <ArrowLeft className="h-4 w-4 me-2" /> Back to Catalog
          </Link>
        </Button>
      </div>
    );
  }

  const whatsappUrl = `https://wa.me/123456789?text=${encodeURIComponent(`${t.product.whatsappMsg} ${product.name} - Model: ${product.id}`)}`;

  return (
    <div className="container mx-auto px-4 py-10">
      <Link to="/products" className="inline-flex items-center text-sm text-muted-foreground hover:text-foreground mb-6 gap-1">
        <ArrowLeft className="h-3 w-3" /> {t.nav.products}
      </Link>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
        {/* Image gallery */}
        <div className="space-y-4">
          <div className="aspect-square rounded-lg overflow-hidden bg-muted">
            <img src={product.images[selectedImage]} alt={product.name} className="w-full h-full object-cover" />
          </div>
          {product.images.length > 1 && (
            <div className="flex gap-3">
              {product.images.map((img, i) => (
                <button
                  key={i}
                  onClick={() => setSelectedImage(i)}
                  className={`w-20 h-20 rounded-md overflow-hidden border-2 transition-colors ${
                    i === selectedImage ? 'border-primary' : 'border-transparent'
                  }`}
                  aria-label={`View image ${i + 1}`}
                >
                  <img src={img} alt="" className="w-full h-full object-cover" />
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Product info */}
        <div className="space-y-6">
          <div>
            <p className="text-sm text-muted-foreground uppercase tracking-wide font-medium">{product.brand}</p>
            <h1 className="font-heading text-2xl md:text-3xl font-bold mt-1">{product.name}</h1>
            <p className="text-sm text-muted-foreground mt-1">{product.category}</p>
          </div>

          <div className="flex items-center gap-3">
            <span className="font-heading text-3xl font-bold text-primary">${product.price.toLocaleString()}</span>
            {product.isNew && <Badge variant="secondary">NEW</Badge>}
          </div>

          <a
            href={whatsappUrl}
            target="_blank"
            rel="noopener noreferrer"
            className="inline-flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-3 rounded-lg transition-colors text-base"
            aria-label="Inquire via WhatsApp"
          >
            <MessageCircle className="h-5 w-5" />
            {t.product.inquire}
          </a>

          <Tabs defaultValue="description" className="mt-6">
            <TabsList className="w-full justify-start">
              <TabsTrigger value="description">{t.product.description}</TabsTrigger>
              <TabsTrigger value="specs">{t.product.specs}</TabsTrigger>
              <TabsTrigger value="features">{t.product.features}</TabsTrigger>
            </TabsList>
            <TabsContent value="description" className="mt-4">
              <p className="text-muted-foreground leading-relaxed">{product.description}</p>
            </TabsContent>
            <TabsContent value="specs" className="mt-4">
              <div className="divide-y">
                {Object.entries(product.specs).map(([key, val]) => (
                  <div key={key} className="flex justify-between py-3 text-sm">
                    <span className="text-muted-foreground">{key}</span>
                    <span className="font-medium">{val}</span>
                  </div>
                ))}
              </div>
            </TabsContent>
            <TabsContent value="features" className="mt-4">
              <ul className="space-y-2">
                {product.features.map((f, i) => (
                  <li key={i} className="flex items-start gap-2 text-sm">
                    <Check className="h-4 w-4 text-primary mt-0.5 shrink-0" />
                    <span>{f}</span>
                  </li>
                ))}
              </ul>
            </TabsContent>
          </Tabs>
        </div>
      </div>
    </div>
  );
};

export default SingleProductPage;
