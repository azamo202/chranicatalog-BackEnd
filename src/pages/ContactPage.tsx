import React from 'react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Phone, Mail, MapPin, Facebook, Instagram, Youtube } from 'lucide-react';

const ContactPage: React.FC = () => {
  const { t } = useLanguage();

  return (
    <div className="container mx-auto px-4 py-10">
      <h1 className="font-heading text-3xl md:text-4xl font-bold mb-10">{t.contact.title}</h1>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <div className="space-y-8">
          <div className="space-y-6">
            <div className="flex items-start gap-4">
              <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                <Phone className="h-5 w-5 text-primary" />
              </div>
              <div>
                <h2 className="font-heading font-semibold">{t.contact.phone}</h2>
                <p className="text-muted-foreground text-sm">+964 750 123 4567</p>
                <p className="text-muted-foreground text-sm">+964 770 234 5678</p>
              </div>
            </div>

            <div className="flex items-start gap-4">
              <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                <Mail className="h-5 w-5 text-primary" />
              </div>
              <div>
                <h2 className="font-heading font-semibold">{t.contact.email}</h2>
                <p className="text-muted-foreground text-sm">info@chrani.com</p>
                <p className="text-muted-foreground text-sm">support@chrani.com</p>
              </div>
            </div>

            <div className="flex items-start gap-4">
              <div className="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center shrink-0">
                <MapPin className="h-5 w-5 text-primary" />
              </div>
              <div>
                <h2 className="font-heading font-semibold">{t.contact.address}</h2>
                <p className="text-muted-foreground text-sm">100m Street, Near Ankawa Intersection<br />Erbil, Kurdistan Region, Iraq</p>
              </div>
            </div>
          </div>

          <div>
            <h2 className="font-heading font-semibold mb-4">{t.contact.social}</h2>
            <div className="flex gap-3">
              <a href="#" className="w-10 h-10 rounded-full bg-secondary flex items-center justify-center hover:bg-primary hover:text-primary-foreground transition-colors" aria-label="Facebook">
                <Facebook className="h-4 w-4" />
              </a>
              <a href="#" className="w-10 h-10 rounded-full bg-secondary flex items-center justify-center hover:bg-primary hover:text-primary-foreground transition-colors" aria-label="Instagram">
                <Instagram className="h-4 w-4" />
              </a>
              <a href="#" className="w-10 h-10 rounded-full bg-secondary flex items-center justify-center hover:bg-primary hover:text-primary-foreground transition-colors" aria-label="YouTube">
                <Youtube className="h-4 w-4" />
              </a>
            </div>
          </div>
        </div>

        <div className="rounded-lg overflow-hidden border h-80 lg:h-auto">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3220.5!2d44.0!3d36.2!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMzbCsDEyJzAwLjAiTiA0NMKwMDAnMDAuMCJF!5e0!3m2!1sen!2siq!4v1600000000000!5m2!1sen!2siq"
            width="100%"
            height="100%"
            style={{ border: 0, minHeight: 320 }}
            allowFullScreen
            loading="lazy"
            referrerPolicy="no-referrer-when-downgrade"
            title="Chrani Location"
          />
        </div>
      </div>
    </div>
  );
};

export default ContactPage;
