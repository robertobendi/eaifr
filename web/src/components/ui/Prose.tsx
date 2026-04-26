import { cn } from '@/lib/cn';

/**
 * Rich-text body renderer. For now treats content as lightweight markdown:
 * paragraphs split on blank lines, `## `/`### ` headings, `- ` bullets,
 * `**bold**` and `[text](url)` inlines. Purposefully narrow — sufficient
 * for slatestack's markdown rich-text field; swap in a full parser later
 * if needed.
 */
function renderInline(text: string): React.ReactNode[] {
  const nodes: React.ReactNode[] = [];
  // Match [label](url) and **bold** alternately.
  const pattern = /(\[([^\]]+)\]\(([^)]+)\))|(\*\*([^*]+)\*\*)/g;
  let last = 0;
  let m: RegExpExecArray | null;
  let i = 0;
  while ((m = pattern.exec(text)) !== null) {
    if (m.index > last) nodes.push(text.slice(last, m.index));
    if (m[1]) {
      nodes.push(
        <a
          key={`l${i++}`}
          href={m[3]}
          className="text-bright-teal underline underline-offset-2"
          target={m[3].startsWith('http') ? '_blank' : undefined}
          rel={m[3].startsWith('http') ? 'noopener noreferrer' : undefined}
        >
          {m[2]}
        </a>,
      );
    } else if (m[4]) {
      nodes.push(<strong key={`b${i++}`} className="text-ink">{m[5]}</strong>);
    }
    last = m.index + m[0].length;
  }
  if (last < text.length) nodes.push(text.slice(last));
  return nodes;
}

export function Prose({
  source,
  className,
}: {
  source: string | undefined | null;
  className?: string;
}) {
  const text = (source || '').trim();
  if (!text) return null;

  const blocks = text.split(/\n\n+/);
  return (
    <div className={cn('prose-eaifr max-w-none space-y-5 text-base leading-relaxed', className)}>
      {blocks.map((block, i) => {
        const b = block.trim();
        if (b.startsWith('## ')) {
          return (
            <h2 key={i} className="mt-10 text-2xl font-bold text-ink">
              {renderInline(b.slice(3))}
            </h2>
          );
        }
        if (b.startsWith('### ')) {
          return (
            <h3 key={i} className="mt-8 text-xl font-bold text-ink">
              {renderInline(b.slice(4))}
            </h3>
          );
        }
        if (b.startsWith('- ')) {
          const items = b.split(/\n/).map((l) => l.replace(/^-\s+/, ''));
          return (
            <ul key={i} className="list-disc space-y-2 pl-5">
              {items.map((it, j) => (
                <li key={j}>{renderInline(it)}</li>
              ))}
            </ul>
          );
        }
        return (
          <p key={i}>
            {b.split('\n').flatMap((line, j, arr) =>
              j < arr.length - 1
                ? [renderInline(line), <br key={`br${j}`} />]
                : [renderInline(line)],
            )}
          </p>
        );
      })}
    </div>
  );
}
