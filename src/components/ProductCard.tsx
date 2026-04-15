import React from 'react';
import { Link } from 'react-router-dom';
import { Product } from '@/data/mockData';
import { LazyLoadImage } from 'react-lazy-load-image-component';

const ProductCard: React.FC<{ product: Product }> = ({ product }) => {
  return (
    <Link
      to={`/products/${product.id}`}
      className="group rounded-lg border bg-card overflow-hidden transition-all hover:shadow-lg hover:-translate-y-1"
      aria-label={product.name}
    >
      <div className="aspect-square overflow-hidden bg-muted relative">
        <LazyLoadImage
          src={product.images[0]}
          alt={product.name}
          className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
          effect="opacity"
        />
        {product.isNew && (
          <span className="absolute top-3 start-3 bg-primary text-primary-foreground text-xs font-semibold px-2.5 py-1 rounded-full">
            NEW
          </span>
        )}
      </div>
      <div className="p-4 space-y-1.5">
        <p className="text-xs text-muted-foreground font-medium uppercase tracking-wide">{product.brand}</p>
        <h3 className="font-heading font-semibold text-sm line-clamp-2 group-hover:text-primary transition-colors">
          {product.name}
        </h3>
        <p className="text-xs text-muted-foreground">{product.category}</p>
        <p className="font-heading font-bold text-lg text-primary">${product.price.toLocaleString()}</p>
      </div>
    </Link>
  );
};

export default ProductCard;
