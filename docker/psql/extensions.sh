#!/bin/bash

set -e

# Perform all actions as $POSTGRES_USER
export PGUSER="$POSTGRES_USER"

# Create the 'laralamma' template db
"${psql[@]}" <<- 'EOSQL'
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_database WHERE datname = 'larallama') THEN
        CREATE DATABASE larallama IS_TEMPLATE true;
    END IF;
END $$;
EOSQL

# Load PostGIS into both template_database and $POSTGRES_DB
for DB in larallama "$POSTGRES_DB"; do
	echo "Loading PostGIS extensions into $DB"
	"${psql[@]}" --dbname="$DB" <<-'EOSQL'
		CREATE EXTENSION IF NOT EXISTS postgis;
		CREATE EXTENSION IF NOT EXISTS vector;
EOSQL
done