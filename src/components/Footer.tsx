import React from 'react';
import { Link } from 'react-router-dom';
import { useLanguage } from '@/i18n/LanguageContext';
import logo from '@/assets/logo.png';

const Footer: React.FC = () => {
  const { t } = useLanguage();

  const links = [
    { to: '/', label: t.nav.home },
    { to: '/products', label: t.nav.products },
    { to: '/support', label: t.nav.support },
    { to: '/about', label: t.nav.about },
    { to: '/contact', label: t.nav.contact },
  ];

  return (
    <footer className="bg-foreground text-background mt-auto" role="contentinfo">
      <div className="container mx-auto px-4 py-12">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div className="space-y-4">
            <div className="flex items-center gap-2">
              <img src={logo} alt="Chrani" className="h-8 w-8 object-contain brightness-0 invert" />
              <span className="font-heading font-bold text-lg">Chrani</span>
            </div>
            <p className="text-sm opacity-70 max-w-xs">
              Premium home appliances for modern living. Quality you can trust.
            </p>
          </div>

          <div>
            <h3 className="font-heading font-semibold mb-4">{t.footer.quickLinks}</h3>
            <ul className="space-y-2">
              {links.map(link => (
                <li key={link.to}>
                  <Link to={link.to} className="text-sm opacity-70 hover:opacity-100 transition-opacity">
                    {link.label}
                  </Link>
                </li>
              ))}
            </ul>
          </div>

          <div>
            <h3 className="font-heading font-semibold mb-4">{t.contact.title}</h3>
            <div className="space-y-2 text-sm opacity-70">
              <p>+964 750 123 4567</p>
              <p>info@chrani.com</p>
              <p>Erbil, Kurdistan Region, Iraq</p>
            </div>
          </div>
        </div>

        <div className="border-t border-background/10 mt-8 pt-6 text-center text-sm opacity-50">
          {t.footer.rights}
        </div>
      </div>
    </footer>
  );
};

export default Footer;
