import React, { createContext, useContext, useState, useEffect, useCallback } from 'react';
import { Language, languages, translations } from './translations';

type LanguageContextType = {
  lang: Language;
  setLang: (lang: Language) => void;
  t: typeof translations['en'];
  dir: 'ltr' | 'rtl';
};

const LanguageContext = createContext<LanguageContextType | undefined>(undefined);

export const LanguageProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [lang, setLangState] = useState<Language>(() => {
    const saved = localStorage.getItem('chrani-lang') as Language;
    return saved && ['en', 'ar', 'ku'].includes(saved) ? saved : 'en';
  });

  const dir = languages.find(l => l.code === lang)?.dir || 'ltr';
  const t = translations[lang];

  const setLang = useCallback((newLang: Language) => {
    setLangState(newLang);
    localStorage.setItem('chrani-lang', newLang);
  }, []);

  useEffect(() => {
    document.documentElement.setAttribute('dir', dir);
    document.documentElement.setAttribute('lang', lang);
  }, [lang, dir]);

  return (
    <LanguageContext.Provider value={{ lang, setLang, t, dir }}>
      {children}
    </LanguageContext.Provider>
  );
};

export const useLanguage = () => {
  const ctx = useContext(LanguageContext);
  if (!ctx) throw new Error('useLanguage must be used within LanguageProvider');
  return ctx;
};
