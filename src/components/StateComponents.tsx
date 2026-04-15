import React from 'react';
import { useLanguage } from '@/i18n/LanguageContext';
import { SearchX, AlertTriangle } from 'lucide-react';
import { Button } from '@/components/ui/button';

export const EmptyState: React.FC<{ message?: string }> = ({ message }) => {
  const { t } = useLanguage();
  return (
    <div className="flex flex-col items-center justify-center py-20 text-center" role="status">
      <SearchX className="h-16 w-16 text-muted-foreground/40 mb-4" />
      <p className="text-muted-foreground text-lg">{message || t.states.empty}</p>
    </div>
  );
};

export const ErrorState: React.FC<{ onRetry?: () => void }> = ({ onRetry }) => {
  const { t } = useLanguage();
  return (
    <div className="flex flex-col items-center justify-center py-20 text-center" role="alert">
      <AlertTriangle className="h-16 w-16 text-destructive/60 mb-4" />
      <p className="text-foreground text-lg font-medium mb-2">{t.states.error}</p>
      {onRetry && (
        <Button variant="outline" onClick={onRetry}>{t.states.retry}</Button>
      )}
    </div>
  );
};
