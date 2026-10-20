FROM php:8.3-cli-alpine
RUN apk add --no-cache curl
WORKDIR /app
COPY router.php /app/router.php
ENV PORT=10000
EXPOSE 10000
CMD ["sh", "-c", "php -S 0.0.0.0:${PORT:-10000} /app/router.php"]
