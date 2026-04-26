# Stage 1 — build
FROM node:20-alpine AS builder
WORKDIR /app
RUN corepack enable && corepack prepare pnpm@10.28.0 --activate

COPY package.json ./
RUN pnpm install --no-frozen-lockfile

COPY tsconfig.json next.config.ts postcss.config.mjs next-env.d.ts ./
COPY src ./src
COPY public ./public

# Build-time CMS URL is baked into server/client bundles for NEXT_PUBLIC_CMS_URL.
# Defaults suitable for docker-compose; override with --build-arg CMS_URL=... in CI/prod.
ARG NEXT_PUBLIC_CMS_URL=http://localhost:3000
ENV NEXT_PUBLIC_CMS_URL=${NEXT_PUBLIC_CMS_URL}
RUN pnpm build

# Stage 2 — runtime
FROM node:20-alpine AS runner
WORKDIR /app
ENV NODE_ENV=production

RUN addgroup -g 1001 -S nodejs && adduser -S nextjs -u 1001

COPY --from=builder --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=builder --chown=nextjs:nodejs /app/.next/static ./.next/static
COPY --from=builder --chown=nextjs:nodejs /app/public ./public

USER nextjs
EXPOSE 3001
ENV PORT=3001
ENV HOSTNAME=0.0.0.0
CMD ["node", "server.js"]
