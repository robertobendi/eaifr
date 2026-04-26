import Link from 'next/link';
import { Eyebrow } from '@/components/brand/Eyebrow';
import { GoldBar } from '@/components/brand/GoldBar';

export default function NotFound() {
  return (
    <section className="panel bg-cream">
      <div className="mx-auto max-w-3xl px-6 py-24 text-center md:px-10 md:py-32">
        <Eyebrow>404 · Page not found</Eyebrow>
        <h1 className="mt-5 font-serif text-4xl font-bold leading-tight text-ink md:text-5xl">
          We couldn&rsquo;t find that page.
        </h1>
        <GoldBar className="mx-auto mt-6" />
        <p className="mt-7 font-serif text-lg leading-relaxed text-mid">
          The page may have moved, or the link may be out of date.
        </p>
        <Link
          href="/"
          className="mt-10 inline-flex items-center gap-2 rounded-full bg-teal px-6 py-3 font-sans text-sm font-bold tracking-wide text-white hover:bg-[#0b6e6e]"
        >
          ← Back to home
        </Link>
      </div>
    </section>
  );
}
