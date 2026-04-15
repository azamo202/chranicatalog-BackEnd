import React from 'react';
import { useLanguage } from '@/i18n/LanguageContext';
import { motion } from 'framer-motion';
import { History, Eye, Target } from 'lucide-react';

const AboutPage: React.FC = () => {
  const { t } = useLanguage();

  const sections = [
    {
      icon: History,
      title: t.about.history,
      content: 'Founded in 2005, Chrani has grown from a small regional distributor into one of the leading home appliance brands in the Middle East. With over 18 years of experience, we have served millions of households with products that combine innovation, durability, and elegant design.',
    },
    {
      icon: Eye,
      title: t.about.vision,
      content: 'To be the most trusted home appliance brand in the region, known for exceptional quality, cutting-edge technology, and outstanding customer service that elevates everyday living.',
    },
    {
      icon: Target,
      title: t.about.mission,
      content: 'We are committed to bringing world-class home appliances to every household, ensuring energy efficiency, reliability, and modern aesthetics. Our mission is to make premium quality accessible while providing unmatched after-sales support.',
    },
  ];

  return (
    <div className="container mx-auto px-4 py-10">
      <motion.div
        initial={{ opacity: 0, y: 20 }}
        animate={{ opacity: 1, y: 0 }}
        className="max-w-3xl mx-auto text-center mb-16"
      >
        <h1 className="font-heading text-3xl md:text-4xl font-bold mb-4">{t.about.title}</h1>
        <p className="text-muted-foreground text-lg">
          Premium home appliances crafted for modern living.
        </p>
      </motion.div>

      <div className="max-w-4xl mx-auto space-y-12">
        {sections.map((s, i) => (
          <motion.article
            key={i}
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            viewport={{ once: true }}
            transition={{ delay: i * 0.1 }}
            className="flex gap-6 items-start"
          >
            <div className="shrink-0 w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center">
              <s.icon className="h-6 w-6 text-primary" />
            </div>
            <div>
              <h2 className="font-heading text-xl font-bold mb-2">{s.title}</h2>
              <p className="text-muted-foreground leading-relaxed">{s.content}</p>
            </div>
          </motion.article>
        ))}
      </div>
    </div>
  );
};

export default AboutPage;
