-- Drop all tables
DO $$ DECLARE
    tabname RECORD;
BEGIN
    FOR tabname IN (SELECT tablename
                    FROM pg_tables
                    WHERE schemaname = current_schema())
        LOOP
            EXECUTE 'DROP TABLE IF EXISTS ' || quote_ident(tabname.tablename) || ' CASCADE';
        END LOOP;
END $$;

-- Drop all sequences
DO $$ DECLARE
    seqname RECORD;
BEGIN
    FOR seqname IN (SELECT sequencename
                    FROM pg_sequences
                    WHERE schemaname = current_schema())
        LOOP
            EXECUTE 'DROP SEQUENCE IF EXISTS ' || quote_ident(seqname.sequencename) || ' CASCADE';
        END LOOP;
END $$;
