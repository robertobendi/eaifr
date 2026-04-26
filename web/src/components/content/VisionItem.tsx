/**
 * A teal circle + checkmark + prose line — the brochure's "our vision"
 * bullet. Three of these in sequence compose the Vision section.
 */
export function VisionItem({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex items-start gap-3 py-1.5">
      <div className="flex h-6 w-6 min-w-6 items-center justify-center rounded-full bg-teal">
        <svg viewBox="0 0 12 12" className="h-3 w-3" aria-hidden="true">
          <path
            d="M2.5 6.5L5 9L9.5 3.5"
            fill="none"
            stroke="#fff"
            strokeWidth="2.5"
            strokeLinecap="round"
            strokeLinejoin="round"
          />
        </svg>
      </div>
      <p className="text-[15px] leading-relaxed text-mid">{children}</p>
    </div>
  );
}
