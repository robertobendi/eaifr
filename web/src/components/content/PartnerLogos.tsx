import { cn } from '@/lib/cn';
import type { Partner } from '@/lib/content';

/**
 * Supporter logo grid — renders the real partner marks as images. When
 * a partner has no logo uploaded we fall back to a typeset name so the
 * grid stays visually coherent. Sized generously (h-14, ~56 px) so the
 * colored logos actually read — the brochure treats them the same way.
 */
export function PartnerLogos({
  partners,
  className,
}: {
  partners: Partner[];
  className?: string;
}) {
  if (!partners.length) return null;
  return (
    <ul
      className={cn(
        'mx-auto grid max-w-5xl grid-cols-2 items-center justify-items-center gap-x-10 gap-y-10 sm:grid-cols-3 md:grid-cols-5',
        className,
      )}
    >
      {partners.map((p) => {
        // Each cell is a fixed-dimension bounding box (176 × 72 px). With
        // trimmed logos + object-contain, wide wordmarks hit the width
        // constraint and square logomarks hit the height constraint —
        // resulting in visually consistent weight across the grid.
        const node = p.logoPath ? (
          // eslint-disable-next-line @next/next/no-img-element
          <img
            src={p.logoPath}
            alt={p.name}
            className="max-h-full max-w-full object-contain"
          />
        ) : (
          <span className="text-center font-sans text-[11.5px] font-black uppercase leading-tight tracking-[0.14em] text-mid/80">
            {p.name}
          </span>
        );

        const cellClass =
          'group flex h-[72px] w-[176px] items-center justify-center opacity-85 transition-opacity duration-300 hover:opacity-100';

        const body = p.url ? (
          <a
            href={p.url}
            target="_blank"
            rel="noopener noreferrer"
            title={p.name}
            aria-label={p.name}
            className={cellClass}
          >
            {node}
          </a>
        ) : (
          <span className={cellClass}>{node}</span>
        );

        return <li key={p.slug}>{body}</li>;
      })}
    </ul>
  );
}
