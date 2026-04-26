import { Eyebrow } from '@/components/brand/Eyebrow';
import { PartnerLogos } from '@/components/content/PartnerLogos';
import { listPartners } from '@/lib/content';

export async function HomePartners() {
  const partners = await listPartners();
  const supporters = partners.filter((p) => p.tier !== 'principal');

  return (
    <section className="panel relative isolate overflow-hidden bg-cream">
      <div className="grain" />
      <div className="mx-auto max-w-4xl px-6 pt-24 text-center md:px-10 md:pt-32">
        <Eyebrow>Supported by</Eyebrow>
        <p className="mx-auto mt-6 max-w-xl font-serif text-[17px] leading-relaxed text-mid">
          EAIFR operates in partnership with UNESCO, ICTP, the University of Rwanda and the
          Government of Rwanda — with the support of international research institutions.
        </p>
      </div>

      <div className="mx-auto max-w-5xl px-6 pb-24 pt-12 md:px-10 md:pb-32">
        <p className="text-center font-sans text-[10px] font-black uppercase tracking-[0.3em] text-teal/80">
          Principal partners
        </p>
        <div className="relative mx-auto mt-6 flex w-full max-w-3xl items-center justify-center rounded-2xl bg-white px-8 py-7 shadow-[0_10px_40px_-20px_rgba(6,30,47,0.25)] ring-1 ring-ink/5">
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img
            src="/logos/principal-logos.png"
            alt="UNESCO · Government of Rwanda · University of Rwanda · ICTP"
            width={1710}
            height={444}
            className="h-auto w-full max-w-[640px]"
          />
        </div>

        {supporters.length > 0 && (
          <>
            <p className="mt-14 text-center font-sans text-[10px] font-black uppercase tracking-[0.3em] text-mid/70">
              In collaboration with
            </p>
            <PartnerLogos partners={supporters} className="mt-6" />
          </>
        )}
      </div>
    </section>
  );
}
