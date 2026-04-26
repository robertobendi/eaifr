/**
 * Thin fetch wrapper around slatestack's public content API.
 *
 * slatestack's public endpoints (from backend/src/modules/content/public.routes.ts):
 *   GET /api/content/:collection
 *     ?where[field]=value        (eq)
 *     ?where[field][op]=value    (ne | gt | gte | lt | lte | contains | in | notIn)
 *     ?sort=-date,title          (comma-separated; leading `-` for desc)
 *     ?limit=10&offset=20
 *     ?fields=title,slug         (projection)
 *     ?populate=area,leads       (reference expansion)
 *
 *   GET /api/content/:collection/:slug
 *     ?populate=...
 *     ?preview=<token>           (draft access; not used in the public site)
 */

const serverUrl = process.env.CMS_URL || 'http://localhost:3000';
const browserUrl = process.env.NEXT_PUBLIC_CMS_URL || 'http://localhost:3000';

const cmsBase = () => (typeof window === 'undefined' ? serverUrl : browserUrl);

export type Where =
  | Record<string, string | number | boolean>
  | Record<string, Partial<Record<'eq' | 'ne' | 'gt' | 'gte' | 'lt' | 'lte' | 'contains' | 'in' | 'notIn', unknown>>>;

export interface QueryOptions {
  where?: Where;
  sort?: string;
  limit?: number;
  offset?: number;
  fields?: string[];
  populate?: string[];
  // Next.js fetch cache hints
  revalidate?: number | false;
  tags?: string[];
}

function buildQuery(opts: QueryOptions = {}): string {
  const params = new URLSearchParams();
  if (opts.where) {
    for (const [key, val] of Object.entries(opts.where)) {
      if (val && typeof val === 'object' && !Array.isArray(val)) {
        for (const [op, v] of Object.entries(val as Record<string, unknown>)) {
          if (v === undefined || v === null) continue;
          params.append(`where[${key}][${op}]`, String(v));
        }
      } else if (val !== undefined && val !== null) {
        params.append(`where[${key}]`, String(val));
      }
    }
  }
  if (opts.sort) params.append('sort', opts.sort);
  if (opts.limit !== undefined) params.append('limit', String(opts.limit));
  if (opts.offset !== undefined) params.append('offset', String(opts.offset));
  if (opts.fields?.length) params.append('fields', opts.fields.join(','));
  if (opts.populate?.length) params.append('populate', opts.populate.join(','));
  const s = params.toString();
  return s ? `?${s}` : '';
}

export interface ListResult<T> {
  data: T[];
  meta: { page: number; limit: number; total: number; totalPages: number };
  pagination: { total: number; limit: number; offset: number; hasMore: boolean };
}

export interface DetailResult<T> {
  data: T;
}

export interface RawEntry {
  id: string;
  collectionId: string;
  slug: string;
  data: Record<string, unknown>;
  status: 'draft' | 'published';
  createdAt: string;
  updatedAt: string;
  publishAt?: string | null;
  position?: number;
}

function nextFetchInit(opts: QueryOptions = {}): RequestInit {
  const init: RequestInit = { headers: { Accept: 'application/json' } };
  // Next.js-specific cache hints
  const next: { revalidate?: number | false; tags?: string[] } = {};
  if (opts.revalidate !== undefined) next.revalidate = opts.revalidate;
  if (opts.tags?.length) next.tags = opts.tags;
  if (next.revalidate !== undefined || next.tags) {
    (init as RequestInit & { next?: typeof next }).next = next;
  }
  return init;
}

const emptyList = <T,>(limit = 20): ListResult<T> => ({
  data: [],
  meta: { page: 1, limit, total: 0, totalPages: 0 },
  pagination: { total: 0, limit, offset: 0, hasMore: false },
});

export async function cmsList<T = RawEntry>(
  collectionSlug: string,
  opts: QueryOptions = {}
): Promise<ListResult<T>> {
  const url = `${cmsBase()}/api/content/${collectionSlug}${buildQuery(opts)}`;
  try {
    const res = await fetch(url, nextFetchInit({ revalidate: 60, ...opts }));
    // 404 → collection not yet created. Non-2xx → treat as transient.
    if (res.status === 404 || !res.ok) return emptyList<T>(opts.limit ?? 20);
    return res.json() as Promise<ListResult<T>>;
  } catch (err) {
    // CMS unreachable (e.g. during `next build` with no backend up).
    // Fall back to an empty list so pages render the empty-state UI
    // instead of crashing the build / request.
    if (process.env.NODE_ENV !== 'production') {
      // eslint-disable-next-line no-console
      console.warn(`[cms] ${url} unreachable:`, (err as Error).message);
    }
    return emptyList<T>(opts.limit ?? 20);
  }
}

export async function cmsOne<T = RawEntry>(
  collectionSlug: string,
  slug: string,
  opts: { populate?: string[]; revalidate?: number | false; tags?: string[] } = {}
): Promise<T | null> {
  const params = new URLSearchParams();
  if (opts.populate?.length) params.append('populate', opts.populate.join(','));
  const qs = params.toString();
  const url = `${cmsBase()}/api/content/${collectionSlug}/${slug}${qs ? `?${qs}` : ''}`;
  try {
    const res = await fetch(url, nextFetchInit({ revalidate: 60, ...opts }));
    if (res.status === 404 || !res.ok) return null;
    const body = (await res.json()) as DetailResult<T>;
    return body.data;
  } catch (err) {
    if (process.env.NODE_ENV !== 'production') {
      // eslint-disable-next-line no-console
      console.warn(`[cms] ${url} unreachable:`, (err as Error).message);
    }
    return null;
  }
}

/** URL for a media file served by slatestack (`/uploads/...`). Returns a safe absolute URL. */
export function mediaUrl(path: string | null | undefined): string | null {
  if (!path) return null;
  if (path.startsWith('http://') || path.startsWith('https://')) return path;
  const base = cmsBase();
  const sep = path.startsWith('/') ? '' : '/';
  return `${base}${sep}${path}`;
}
