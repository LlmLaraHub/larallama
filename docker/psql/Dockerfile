FROM postgres:15

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        postgresql-15-pgvector \
        postgresql-15-postgis \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /docker-entrypoint-initdb.d
COPY ./extensions.sh /docker-entrypoint-initdb.d/10_postgis.sh