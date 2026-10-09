import { PageHeader } from '@/components/shared/PageHeader';

export default function PlaceholderPage({ title }: { title: string }) {
  return (
    <div>
      <PageHeader title={title} description="Halaman ini dibuat di tahap berikutnya." />
    </div>
  );
}