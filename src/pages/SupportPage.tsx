import React from 'react';
import { useLanguage } from '@/i18n/LanguageContext';
import { serviceCenters, userManuals, videoTutorials } from '@/data/mockData';
import { FileDown, Play, MapPin, Phone } from 'lucide-react';
import { Button } from '@/components/ui/button';

const SupportPage: React.FC = () => {
  const { t } = useLanguage();

  return (
    <div className="container mx-auto px-4 py-10">
      <h1 className="font-heading text-3xl md:text-4xl font-bold mb-10">{t.support.title}</h1>

      {/* Manuals */}
      <section className="mb-16" aria-label="User manuals">
        <h2 className="font-heading text-2xl font-bold mb-6">{t.support.manuals}</h2>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          {userManuals.map(m => (
            <div key={m.id} className="border rounded-lg p-5 hover:shadow-md transition-shadow bg-card">
              <FileDown className="h-10 w-10 text-primary mb-3" />
              <h3 className="font-heading font-semibold text-sm mb-1">{m.title}</h3>
              <p className="text-xs text-muted-foreground mb-3">{m.size} · {m.category}</p>
              <Button variant="outline" size="sm" className="w-full gap-2">
                <FileDown className="h-3 w-3" /> {t.support.download}
              </Button>
            </div>
          ))}
        </div>
      </section>

      {/* Videos */}
      <section className="mb-16" aria-label="Video tutorials">
        <h2 className="font-heading text-2xl font-bold mb-6">{t.support.videos}</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {videoTutorials.map(v => (
            <div key={v.id} className="border rounded-lg overflow-hidden bg-card">
              <div className="aspect-video relative bg-muted flex items-center justify-center">
                <iframe
                  src={`https://www.youtube.com/embed/${v.youtubeId}`}
                  title={v.title}
                  className="absolute inset-0 w-full h-full"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                  loading="lazy"
                />
              </div>
              <div className="p-4">
                <h3 className="font-heading font-semibold text-sm">{v.title}</h3>
              </div>
            </div>
          ))}
        </div>
      </section>

      {/* Service Centers */}
      <section aria-label="Service centers">
        <h2 className="font-heading text-2xl font-bold mb-6">{t.support.centers}</h2>
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
          {serviceCenters.map(c => (
            <div key={c.id} className="border rounded-lg p-6 bg-card hover:shadow-md transition-shadow">
              <h3 className="font-heading font-semibold mb-3">{c.name}</h3>
              <div className="space-y-2 text-sm text-muted-foreground">
                <p className="flex items-start gap-2"><MapPin className="h-4 w-4 text-primary shrink-0 mt-0.5" /> {c.address}</p>
                <p className="flex items-center gap-2"><Phone className="h-4 w-4 text-primary shrink-0" /> {c.phone}</p>
              </div>
            </div>
          ))}
        </div>
      </section>
    </div>
  );
};

export default SupportPage;
