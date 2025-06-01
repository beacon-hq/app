import { Button } from '@/Components/ui/button';
import { Head } from '@inertiajs/react';
import { useEffect } from 'react';

interface ErrorPageProps {
  status: number;
  message?: string;
}

export default function Error({ status, message }: ErrorPageProps) {
  const title = {
    503: 'Service Unavailable',
    500: 'Server Error',
    404: 'Page Not Found',
    403: 'Forbidden',
    400: 'Bad Request',
  }[status];

  const description = message || {
    503: 'Sorry, we are doing some maintenance. Please check back soon.',
    500: 'Oops! Something went wrong on our servers.',
    404: 'Sorry, the page you are looking for could not be found.',
    403: 'Sorry, you are forbidden from accessing this page.',
    400: 'The request could not be processed.',
  }[status];

  useEffect(() => {
    document.body.classList.add('error-page');
    return () => {
      document.body.classList.remove('error-page');
    };
  }, []);

  return (
    <>
      <Head title={title} />
      <div className="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
        <div className="max-w-xl w-full px-6 py-12 text-center">
          <h1 className="text-9xl font-bold text-primary mb-4">{status}</h1>
          <h2 className="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
            {title}
          </h2>
          <p className="text-gray-600 dark:text-gray-400 mb-8">{description}</p>
          <div className="flex justify-center gap-4">
            <Button
              onClick={() => window.history.back()}
              variant="outline"
            >
              Go Back
            </Button>
            <Button
              onClick={() => window.location.href = '/'}
            >
              Go Home
            </Button>
          </div>
        </div>
      </div>
    </>
  );
}
