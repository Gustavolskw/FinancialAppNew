FROM node:20-alpine AS development-dependencies-env
COPY . /app
WORKDIR /app
RUN npm ci
RUN chmod +x /app/docker-entrypoint.sh

FROM development-dependencies-env AS development-env
ENV FRONTEND_RUNTIME_MODE=development
ENV PORT=5173
EXPOSE 5173
CMD ["sh", "/app/docker-entrypoint.sh"]

FROM node:20-alpine AS production-dependencies-env
COPY ./package.json package-lock.json /app/
WORKDIR /app
RUN npm ci --omit=dev

FROM node:20-alpine AS build-env
COPY . /app/
COPY --from=development-dependencies-env /app/node_modules /app/node_modules
WORKDIR /app
RUN npm run build

FROM node:20-alpine AS production-env
COPY ./package.json package-lock.json /app/
COPY ./docker-entrypoint.sh /app/docker-entrypoint.sh
COPY --from=production-dependencies-env /app/node_modules /app/node_modules
COPY --from=build-env /app/build /app/build
WORKDIR /app
RUN chmod +x /app/docker-entrypoint.sh
ENV FRONTEND_RUNTIME_MODE=production
ENV NODE_ENV=production
ENV PORT=5173
EXPOSE 5173
CMD ["sh", "/app/docker-entrypoint.sh"]
