--
-- PostgreSQL database dump
--

-- Dumped from database version 17.0 (Laravel Herd)
-- Dumped by pg_dump version 17.5

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: beacon; Type: SCHEMA; Schema: -; Owner: -
--

CREATE SCHEMA beacon;


--
-- Name: SCHEMA beacon; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON SCHEMA beacon IS 'standard public schema';


--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA beacon;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


--
-- Name: generate_ulid(); Type: FUNCTION; Schema: beacon; Owner: -
--

CREATE FUNCTION beacon.generate_ulid() RETURNS text
    LANGUAGE plpgsql
    AS $$
DECLARE
    -- Crockford's Base32
    encoding   BYTEA = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
    timestamp  BYTEA = E'\\000\\000\\000\\000\\000\\000';
    output     TEXT = '';

    unix_time  BIGINT;
    ulid       BYTEA;
BEGIN
    -- 6 timestamp bytes
    unix_time = (EXTRACT(EPOCH FROM CLOCK_TIMESTAMP()) * 1000)::BIGINT;
    timestamp = SET_BYTE(timestamp, 0, (unix_time >> 40)::BIT(8)::INTEGER);
    timestamp = SET_BYTE(timestamp, 1, (unix_time >> 32)::BIT(8)::INTEGER);
    timestamp = SET_BYTE(timestamp, 2, (unix_time >> 24)::BIT(8)::INTEGER);
    timestamp = SET_BYTE(timestamp, 3, (unix_time >> 16)::BIT(8)::INTEGER);
    timestamp = SET_BYTE(timestamp, 4, (unix_time >> 8)::BIT(8)::INTEGER);
    timestamp = SET_BYTE(timestamp, 5, unix_time::BIT(8)::INTEGER);

    -- 10 entropy bytes
    ulid = timestamp || gen_random_bytes(10);

    -- Encode the timestamp
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 0) & 224) >> 5));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 0) & 31)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 1) & 248) >> 3));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 1) & 7) << 2) | ((GET_BYTE(ulid, 2) & 192) >> 6)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 2) & 62) >> 1));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 2) & 1) << 4) | ((GET_BYTE(ulid, 3) & 240) >> 4)));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 3) & 15) << 1) | ((GET_BYTE(ulid, 4) & 128) >> 7)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 4) & 124) >> 2));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 4) & 3) << 3) | ((GET_BYTE(ulid, 5) & 224) >> 5)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 5) & 31)));

    -- Encode the entropy
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 6) & 248) >> 3));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 6) & 7) << 2) | ((GET_BYTE(ulid, 7) & 192) >> 6)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 7) & 62) >> 1));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 7) & 1) << 4) | ((GET_BYTE(ulid, 8) & 240) >> 4)));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 8) & 15) << 1) | ((GET_BYTE(ulid, 9) & 128) >> 7)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 9) & 124) >> 2));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 9) & 3) << 3) | ((GET_BYTE(ulid, 10) & 224) >> 5)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 10) & 31)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 11) & 248) >> 3));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 11) & 7) << 2) | ((GET_BYTE(ulid, 12) & 192) >> 6)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 12) & 62) >> 1));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 12) & 1) << 4) | ((GET_BYTE(ulid, 13) & 240) >> 4)));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 13) & 15) << 1) | ((GET_BYTE(ulid, 14) & 128) >> 7)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 14) & 124) >> 2));
    output = output || CHR(GET_BYTE(encoding, ((GET_BYTE(ulid, 14) & 3) << 3) | ((GET_BYTE(ulid, 15) & 224) >> 5)));
    output = output || CHR(GET_BYTE(encoding, (GET_BYTE(ulid, 15) & 31)));

    RETURN output;
END
$$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: access_tokens; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    token character varying(64) NOT NULL,
    plain_text_suffix character varying(5) NOT NULL,
    abilities jsonb,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    token_value text NOT NULL
);


--
-- Name: activity_log; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.activity_log (
    id bigint NOT NULL,
    organization_id character(26) NOT NULL,
    team_id character(26) NOT NULL,
    log_name character varying(255),
    description text NOT NULL,
    subject_type character varying(255),
    subject_id character(26),
    causer_type character varying(255),
    causer_id character(26),
    properties json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    event character varying(255),
    batch_uuid uuid
);


--
-- Name: activity_log_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.activity_log_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: activity_log_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.activity_log_id_seq OWNED BY beacon.activity_log.id;


--
-- Name: application_feature_flag; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.application_feature_flag (
    id bigint NOT NULL,
    application_id character(26) NOT NULL,
    feature_flag_id character(26) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: application_feature_flag_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.application_feature_flag_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: application_feature_flag_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.application_feature_flag_id_seq OWNED BY beacon.application_feature_flag.id;


--
-- Name: applications; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.applications (
    id character(26) NOT NULL,
    team_id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    display_name character varying(255) NOT NULL,
    description text,
    color character varying(255) DEFAULT '#e3e3e3'::character varying NOT NULL,
    last_seen_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cache; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: environment_feature_flag; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.environment_feature_flag (
    id bigint NOT NULL,
    feature_flag_id character(26) NOT NULL,
    environment_id character(26) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: environment_feature_flag_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.environment_feature_flag_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: environment_feature_flag_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.environment_feature_flag_id_seq OWNED BY beacon.environment_feature_flag.id;


--
-- Name: environments; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.environments (
    id character(26) NOT NULL,
    team_id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    color character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    last_seen_at timestamp(0) without time zone
);


--
-- Name: failed_jobs; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.failed_jobs_id_seq OWNED BY beacon.failed_jobs.id;


--
-- Name: feature_flag_feature_flag_status; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.feature_flag_feature_flag_status (
    id character(26) NOT NULL,
    feature_flag_id character(26) NOT NULL,
    feature_flag_status_id character(26) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: feature_flag_statuses; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.feature_flag_statuses (
    id character(26) NOT NULL,
    application_id character(26) NOT NULL,
    environment_id character(26) NOT NULL,
    feature_flag_id character(26) NOT NULL,
    status boolean NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    definition jsonb,
    rollout_percentage smallint,
    rollout_strategy character varying(255),
    rollout_context jsonb,
    variants jsonb,
    variant_strategy character varying(255) DEFAULT 'random'::character varying,
    variant_context jsonb
);


--
-- Name: feature_flag_tag; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.feature_flag_tag (
    id bigint NOT NULL,
    feature_flag_id character(26) NOT NULL,
    tag_id character(26) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: feature_flag_tag_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.feature_flag_tag_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: feature_flag_tag_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.feature_flag_tag_id_seq OWNED BY beacon.feature_flag_tag.id;


--
-- Name: feature_flag_usages; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.feature_flag_usages (
    id character(26) NOT NULL,
    team_id character(26) NOT NULL,
    feature_flag_id character(26),
    application_id character(26),
    environment_id character(26),
    active boolean NOT NULL,
    value json,
    evaluated_at timestamp(0) without time zone NOT NULL,
    feature_flag_name character varying(255) NOT NULL,
    application_name character varying(255),
    environment_name character varying(255)
);


--
-- Name: feature_flags; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.feature_flags (
    id character(26) NOT NULL,
    team_id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    last_seen_at timestamp(0) without time zone,
    feature_type_id character(26) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    status boolean DEFAULT false NOT NULL,
    completed_at timestamp(0) without time zone
);


--
-- Name: feature_types; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.feature_types (
    id character(26) NOT NULL,
    team_id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    temporary boolean NOT NULL,
    color character varying(255) DEFAULT '#e3e3e3'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    icon character varying(255),
    is_default boolean DEFAULT false NOT NULL
);


--
-- Name: invites; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.invites (
    id character(26) NOT NULL,
    user_id bigint NOT NULL,
    team_id character(26) NOT NULL,
    email character varying(255) NOT NULL,
    role character varying(255) NOT NULL,
    expires_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    organization_id character(26) NOT NULL
);


--
-- Name: job_batches; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.jobs_id_seq OWNED BY beacon.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.migrations_id_seq OWNED BY beacon.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


--
-- Name: model_has_roles; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


--
-- Name: organization_user; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.organization_user (
    organization_id character(26) NOT NULL,
    user_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: organizations; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.organizations (
    id character(26) NOT NULL,
    owner_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    name character varying(255) NOT NULL,
    stripe_id character varying(255),
    pm_type character varying(255),
    pm_last_four character varying(4),
    trial_ends_at timestamp(0) without time zone,
    onboarded_at timestamp(0) without time zone
);


--
-- Name: password_reset_tokens; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: permissions; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.permissions_id_seq OWNED BY beacon.permissions.id;


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.personal_access_tokens_id_seq OWNED BY beacon.access_tokens.id;


--
-- Name: policies; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.policies (
    id character(26) NOT NULL,
    team_id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    definition jsonb NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: role_has_permissions; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


--
-- Name: roles; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.roles_id_seq OWNED BY beacon.roles.id;


--
-- Name: sessions; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: stripe_prices; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.stripe_prices (
    id character(26) NOT NULL,
    stripe_id character varying(255) NOT NULL,
    stripe_product_id character(26),
    name character varying(255) NOT NULL,
    unit_amount integer DEFAULT 0,
    active boolean NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: stripe_products; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.stripe_products (
    id character(26) NOT NULL,
    stripe_id character varying(255) NOT NULL,
    stripe_base_price_id character(26),
    stripe_metered_price_id character(26),
    name character varying(255) NOT NULL,
    description text NOT NULL,
    active boolean NOT NULL,
    entitlements jsonb NOT NULL,
    metadata jsonb NOT NULL,
    "order" integer NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: subscription_items; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.subscription_items (
    id bigint NOT NULL,
    subscription_id bigint NOT NULL,
    stripe_id character varying(255) NOT NULL,
    stripe_product character varying(255) NOT NULL,
    stripe_price character varying(255) NOT NULL,
    quantity integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: subscription_items_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.subscription_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: subscription_items_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.subscription_items_id_seq OWNED BY beacon.subscription_items.id;


--
-- Name: subscriptions; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.subscriptions (
    id bigint NOT NULL,
    organization_id character(26) NOT NULL,
    type character varying(255) NOT NULL,
    stripe_id character varying(255) NOT NULL,
    stripe_status character varying(255) NOT NULL,
    stripe_price character varying(255),
    quantity integer,
    trial_ends_at timestamp(0) without time zone,
    ends_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: subscriptions_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.subscriptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: subscriptions_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.subscriptions_id_seq OWNED BY beacon.subscriptions.id;


--
-- Name: tags; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.tags (
    id character(26) NOT NULL,
    team_id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    color character varying(255) DEFAULT '#e3e3e3'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: team_user; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.team_user (
    user_id bigint NOT NULL,
    team_id character(26) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: teams; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.teams (
    id character(26) NOT NULL,
    name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    color character varying(255),
    icon character varying(255),
    organization_id character varying(255) NOT NULL
);


--
-- Name: telescope_entries; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.telescope_entries (
    sequence bigint NOT NULL,
    uuid uuid NOT NULL,
    batch_id uuid NOT NULL,
    family_hash character varying(255),
    should_display_on_index boolean DEFAULT true NOT NULL,
    type character varying(20) NOT NULL,
    content text NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: telescope_entries_sequence_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.telescope_entries_sequence_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: telescope_entries_sequence_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.telescope_entries_sequence_seq OWNED BY beacon.telescope_entries.sequence;


--
-- Name: telescope_entries_tags; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.telescope_entries_tags (
    entry_uuid uuid NOT NULL,
    tag character varying(255) NOT NULL
);


--
-- Name: telescope_monitoring; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.telescope_monitoring (
    tag character varying(255) NOT NULL
);


--
-- Name: users; Type: TABLE; Schema: beacon; Owner: -
--

CREATE TABLE beacon.users (
    id bigint NOT NULL,
    first_name character varying(255) NOT NULL,
    last_name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    avatar_url character varying(255),
    two_factor_secret text,
    two_factor_recovery_codes text,
    two_factor_confirmed_at timestamp(0) without time zone,
    theme character varying(255) DEFAULT 'system'::character varying NOT NULL,
    status character varying(255) NOT NULL
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: beacon; Owner: -
--

CREATE SEQUENCE beacon.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: beacon; Owner: -
--

ALTER SEQUENCE beacon.users_id_seq OWNED BY beacon.users.id;


--
-- Name: access_tokens id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.access_tokens ALTER COLUMN id SET DEFAULT nextval('beacon.personal_access_tokens_id_seq'::regclass);


--
-- Name: activity_log id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.activity_log ALTER COLUMN id SET DEFAULT nextval('beacon.activity_log_id_seq'::regclass);


--
-- Name: application_feature_flag id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.application_feature_flag ALTER COLUMN id SET DEFAULT nextval('beacon.application_feature_flag_id_seq'::regclass);


--
-- Name: environment_feature_flag id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.environment_feature_flag ALTER COLUMN id SET DEFAULT nextval('beacon.environment_feature_flag_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.failed_jobs ALTER COLUMN id SET DEFAULT nextval('beacon.failed_jobs_id_seq'::regclass);


--
-- Name: feature_flag_tag id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_tag ALTER COLUMN id SET DEFAULT nextval('beacon.feature_flag_tag_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.jobs ALTER COLUMN id SET DEFAULT nextval('beacon.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.migrations ALTER COLUMN id SET DEFAULT nextval('beacon.migrations_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.permissions ALTER COLUMN id SET DEFAULT nextval('beacon.permissions_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.roles ALTER COLUMN id SET DEFAULT nextval('beacon.roles_id_seq'::regclass);


--
-- Name: subscription_items id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.subscription_items ALTER COLUMN id SET DEFAULT nextval('beacon.subscription_items_id_seq'::regclass);


--
-- Name: subscriptions id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.subscriptions ALTER COLUMN id SET DEFAULT nextval('beacon.subscriptions_id_seq'::regclass);


--
-- Name: telescope_entries sequence; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.telescope_entries ALTER COLUMN sequence SET DEFAULT nextval('beacon.telescope_entries_sequence_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.users ALTER COLUMN id SET DEFAULT nextval('beacon.users_id_seq'::regclass);


--
-- Name: activity_log activity_log_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.activity_log
    ADD CONSTRAINT activity_log_pkey PRIMARY KEY (id);


--
-- Name: application_feature_flag application_feature_flag_application_id_feature_flag_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.application_feature_flag
    ADD CONSTRAINT application_feature_flag_application_id_feature_flag_id_unique UNIQUE (application_id, feature_flag_id);


--
-- Name: application_feature_flag application_feature_flag_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.application_feature_flag
    ADD CONSTRAINT application_feature_flag_pkey PRIMARY KEY (id);


--
-- Name: applications applications_display_name_team_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.applications
    ADD CONSTRAINT applications_display_name_team_id_unique UNIQUE (display_name, team_id);


--
-- Name: applications applications_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.applications
    ADD CONSTRAINT applications_pkey PRIMARY KEY (id);


--
-- Name: applications applications_team_id_name_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.applications
    ADD CONSTRAINT applications_team_id_name_unique UNIQUE (team_id, name);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: environment_feature_flag environment_feature_flag_feature_flag_id_environment_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.environment_feature_flag
    ADD CONSTRAINT environment_feature_flag_feature_flag_id_environment_id_unique UNIQUE (feature_flag_id, environment_id);


--
-- Name: environment_feature_flag environment_feature_flag_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.environment_feature_flag
    ADD CONSTRAINT environment_feature_flag_pkey PRIMARY KEY (id);


--
-- Name: environments environments_name_team_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.environments
    ADD CONSTRAINT environments_name_team_id_unique UNIQUE (name, team_id);


--
-- Name: environments environments_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.environments
    ADD CONSTRAINT environments_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: feature_flag_feature_flag_status feature_flag_feature_flag_status_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_feature_flag_status
    ADD CONSTRAINT feature_flag_feature_flag_status_pkey PRIMARY KEY (id);


--
-- Name: feature_flag_statuses feature_flag_statuses_feature_flag_id_application_id_environmen; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_statuses
    ADD CONSTRAINT feature_flag_statuses_feature_flag_id_application_id_environmen UNIQUE (feature_flag_id, application_id, environment_id);


--
-- Name: feature_flag_statuses feature_flag_statuses_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_statuses
    ADD CONSTRAINT feature_flag_statuses_pkey PRIMARY KEY (id);


--
-- Name: feature_flag_tag feature_flag_tag_feature_flag_id_tag_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_tag
    ADD CONSTRAINT feature_flag_tag_feature_flag_id_tag_id_unique UNIQUE (feature_flag_id, tag_id);


--
-- Name: feature_flag_tag feature_flag_tag_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_tag
    ADD CONSTRAINT feature_flag_tag_pkey PRIMARY KEY (id);


--
-- Name: feature_flag_usages feature_flag_usages_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_usages
    ADD CONSTRAINT feature_flag_usages_pkey PRIMARY KEY (id);


--
-- Name: feature_flags feature_flags_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flags
    ADD CONSTRAINT feature_flags_pkey PRIMARY KEY (id);


--
-- Name: feature_types feature_types_name_team_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_types
    ADD CONSTRAINT feature_types_name_team_id_unique UNIQUE (name, team_id);


--
-- Name: feature_types feature_types_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_types
    ADD CONSTRAINT feature_types_pkey PRIMARY KEY (id);


--
-- Name: invites invites_email_team_id_organization_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.invites
    ADD CONSTRAINT invites_email_team_id_organization_id_unique UNIQUE (email, team_id, organization_id);


--
-- Name: invites invites_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.invites
    ADD CONSTRAINT invites_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: policies policies_name_team_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.policies
    ADD CONSTRAINT policies_name_team_id_unique UNIQUE (name, team_id);


--
-- Name: policies policies_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.policies
    ADD CONSTRAINT policies_pkey PRIMARY KEY (id);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: stripe_prices stripe_prices_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.stripe_prices
    ADD CONSTRAINT stripe_prices_pkey PRIMARY KEY (id);


--
-- Name: stripe_products stripe_products_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.stripe_products
    ADD CONSTRAINT stripe_products_pkey PRIMARY KEY (id);


--
-- Name: subscription_items subscription_items_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.subscription_items
    ADD CONSTRAINT subscription_items_pkey PRIMARY KEY (id);


--
-- Name: subscription_items subscription_items_stripe_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.subscription_items
    ADD CONSTRAINT subscription_items_stripe_id_unique UNIQUE (stripe_id);


--
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- Name: subscriptions subscriptions_stripe_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.subscriptions
    ADD CONSTRAINT subscriptions_stripe_id_unique UNIQUE (stripe_id);


--
-- Name: tags tags_name_team_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.tags
    ADD CONSTRAINT tags_name_team_id_unique UNIQUE (name, team_id);


--
-- Name: tags tags_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.tags
    ADD CONSTRAINT tags_pkey PRIMARY KEY (id);


--
-- Name: teams teams_name_organization_id_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.teams
    ADD CONSTRAINT teams_name_organization_id_unique UNIQUE (name, organization_id);


--
-- Name: teams teams_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.teams
    ADD CONSTRAINT teams_pkey PRIMARY KEY (id);


--
-- Name: telescope_entries telescope_entries_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.telescope_entries
    ADD CONSTRAINT telescope_entries_pkey PRIMARY KEY (sequence);


--
-- Name: telescope_entries_tags telescope_entries_tags_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.telescope_entries_tags
    ADD CONSTRAINT telescope_entries_tags_pkey PRIMARY KEY (entry_uuid, tag);


--
-- Name: telescope_entries telescope_entries_uuid_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.telescope_entries
    ADD CONSTRAINT telescope_entries_uuid_unique UNIQUE (uuid);


--
-- Name: telescope_monitoring telescope_monitoring_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.telescope_monitoring
    ADD CONSTRAINT telescope_monitoring_pkey PRIMARY KEY (tag);


--
-- Name: organizations tenants_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.organizations
    ADD CONSTRAINT tenants_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: activity_log_organization_id_team_id_log_name_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX activity_log_organization_id_team_id_log_name_index ON beacon.activity_log USING btree (organization_id, team_id, log_name);


--
-- Name: activity_log_team_id_log_name_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX activity_log_team_id_log_name_index ON beacon.activity_log USING btree (team_id, log_name);


--
-- Name: application_feature_flag_application_id_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX application_feature_flag_application_id_index ON beacon.application_feature_flag USING btree (application_id);


--
-- Name: causer; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX causer ON beacon.activity_log USING btree (causer_type, causer_id);


--
-- Name: environment_feature_flag_environment_id_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX environment_feature_flag_environment_id_index ON beacon.environment_feature_flag USING btree (environment_id);


--
-- Name: evaluated_at; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX evaluated_at ON beacon.feature_flag_usages USING btree (team_id);


--
-- Name: feature_flag_tag_feature_flag_id_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX feature_flag_tag_feature_flag_id_index ON beacon.feature_flag_tag USING btree (feature_flag_id);


--
-- Name: feature_flag_usages_team_id_feature_flag_id_application_id_envi; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX feature_flag_usages_team_id_feature_flag_id_application_id_envi ON beacon.feature_flag_usages USING btree (team_id, feature_flag_id, application_id, environment_id);


--
-- Name: feature_flags_name_team_id_unique; Type: INDEX; Schema: beacon; Owner: -
--

CREATE UNIQUE INDEX feature_flags_name_team_id_unique ON beacon.feature_flags USING btree (name, team_id) WHERE (completed_at IS NULL);


--
-- Name: feature_types_is_default_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX feature_types_is_default_index ON beacon.feature_types USING btree (is_default);


--
-- Name: invites_email_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX invites_email_index ON beacon.invites USING btree (email);


--
-- Name: invites_team_id_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX invites_team_id_index ON beacon.invites USING btree (team_id);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX jobs_queue_index ON beacon.jobs USING btree (queue);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON beacon.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX model_has_roles_model_id_model_type_index ON beacon.model_has_roles USING btree (model_id, model_type);


--
-- Name: organizations_stripe_id_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX organizations_stripe_id_index ON beacon.organizations USING btree (stripe_id);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON beacon.access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX sessions_last_activity_index ON beacon.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX sessions_user_id_index ON beacon.sessions USING btree (user_id);


--
-- Name: subject; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX subject ON beacon.activity_log USING btree (subject_type, subject_id);


--
-- Name: subscription_items_subscription_id_stripe_price_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX subscription_items_subscription_id_stripe_price_index ON beacon.subscription_items USING btree (subscription_id, stripe_price);


--
-- Name: subscriptions_organization_id_stripe_status_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX subscriptions_organization_id_stripe_status_index ON beacon.subscriptions USING btree (organization_id, stripe_status);


--
-- Name: telescope_entries_batch_id_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX telescope_entries_batch_id_index ON beacon.telescope_entries USING btree (batch_id);


--
-- Name: telescope_entries_created_at_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX telescope_entries_created_at_index ON beacon.telescope_entries USING btree (created_at);


--
-- Name: telescope_entries_family_hash_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX telescope_entries_family_hash_index ON beacon.telescope_entries USING btree (family_hash);


--
-- Name: telescope_entries_tags_tag_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX telescope_entries_tags_tag_index ON beacon.telescope_entries_tags USING btree (tag);


--
-- Name: telescope_entries_type_should_display_on_index_index; Type: INDEX; Schema: beacon; Owner: -
--

CREATE INDEX telescope_entries_type_should_display_on_index_index ON beacon.telescope_entries USING btree (type, should_display_on_index);


--
-- Name: application_feature_flag application_feature_flag_application_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.application_feature_flag
    ADD CONSTRAINT application_feature_flag_application_id_foreign FOREIGN KEY (application_id) REFERENCES beacon.applications(id) ON DELETE CASCADE;


--
-- Name: application_feature_flag application_feature_flag_feature_flag_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.application_feature_flag
    ADD CONSTRAINT application_feature_flag_feature_flag_id_foreign FOREIGN KEY (feature_flag_id) REFERENCES beacon.feature_flags(id) ON DELETE CASCADE;


--
-- Name: applications applications_team_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.applications
    ADD CONSTRAINT applications_team_id_foreign FOREIGN KEY (team_id) REFERENCES beacon.teams(id) ON DELETE CASCADE;


--
-- Name: environment_feature_flag environment_feature_flag_environment_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.environment_feature_flag
    ADD CONSTRAINT environment_feature_flag_environment_id_foreign FOREIGN KEY (environment_id) REFERENCES beacon.environments(id) ON DELETE CASCADE;


--
-- Name: environment_feature_flag environment_feature_flag_feature_flag_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.environment_feature_flag
    ADD CONSTRAINT environment_feature_flag_feature_flag_id_foreign FOREIGN KEY (feature_flag_id) REFERENCES beacon.feature_flags(id) ON DELETE CASCADE;


--
-- Name: environments environments_team_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.environments
    ADD CONSTRAINT environments_team_id_foreign FOREIGN KEY (team_id) REFERENCES beacon.teams(id) ON DELETE CASCADE;


--
-- Name: feature_flag_feature_flag_status feature_flag_feature_flag_status_feature_flag_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_feature_flag_status
    ADD CONSTRAINT feature_flag_feature_flag_status_feature_flag_id_foreign FOREIGN KEY (feature_flag_id) REFERENCES beacon.feature_flags(id) ON DELETE CASCADE;


--
-- Name: feature_flag_feature_flag_status feature_flag_feature_flag_status_feature_flag_status_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_feature_flag_status
    ADD CONSTRAINT feature_flag_feature_flag_status_feature_flag_status_id_foreign FOREIGN KEY (feature_flag_status_id) REFERENCES beacon.feature_flag_statuses(id) ON DELETE CASCADE;


--
-- Name: feature_flag_statuses feature_flag_statuses_application_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_statuses
    ADD CONSTRAINT feature_flag_statuses_application_id_foreign FOREIGN KEY (application_id) REFERENCES beacon.applications(id) ON DELETE CASCADE;


--
-- Name: feature_flag_statuses feature_flag_statuses_environment_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_statuses
    ADD CONSTRAINT feature_flag_statuses_environment_id_foreign FOREIGN KEY (environment_id) REFERENCES beacon.environments(id) ON DELETE CASCADE;


--
-- Name: feature_flag_statuses feature_flag_statuses_feature_flag_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_statuses
    ADD CONSTRAINT feature_flag_statuses_feature_flag_id_foreign FOREIGN KEY (feature_flag_id) REFERENCES beacon.feature_flags(id) ON DELETE CASCADE;


--
-- Name: feature_flag_tag feature_flag_tag_feature_flag_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_tag
    ADD CONSTRAINT feature_flag_tag_feature_flag_id_foreign FOREIGN KEY (feature_flag_id) REFERENCES beacon.feature_flags(id) ON DELETE CASCADE;


--
-- Name: feature_flag_tag feature_flag_tag_tag_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_tag
    ADD CONSTRAINT feature_flag_tag_tag_id_foreign FOREIGN KEY (tag_id) REFERENCES beacon.tags(id) ON DELETE CASCADE;


--
-- Name: feature_flag_usages feature_flag_usages_application_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_usages
    ADD CONSTRAINT feature_flag_usages_application_id_foreign FOREIGN KEY (application_id) REFERENCES beacon.applications(id) ON DELETE CASCADE;


--
-- Name: feature_flag_usages feature_flag_usages_environment_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_usages
    ADD CONSTRAINT feature_flag_usages_environment_id_foreign FOREIGN KEY (environment_id) REFERENCES beacon.environments(id) ON DELETE CASCADE;


--
-- Name: feature_flag_usages feature_flag_usages_feature_flag_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_usages
    ADD CONSTRAINT feature_flag_usages_feature_flag_id_foreign FOREIGN KEY (feature_flag_id) REFERENCES beacon.feature_flags(id) ON DELETE CASCADE;


--
-- Name: feature_flag_usages feature_flag_usages_team_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flag_usages
    ADD CONSTRAINT feature_flag_usages_team_id_foreign FOREIGN KEY (team_id) REFERENCES beacon.teams(id) ON DELETE CASCADE;


--
-- Name: feature_flags feature_flags_feature_type_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flags
    ADD CONSTRAINT feature_flags_feature_type_id_foreign FOREIGN KEY (feature_type_id) REFERENCES beacon.feature_types(id);


--
-- Name: feature_flags feature_flags_team_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_flags
    ADD CONSTRAINT feature_flags_team_id_foreign FOREIGN KEY (team_id) REFERENCES beacon.teams(id) ON DELETE CASCADE;


--
-- Name: feature_types feature_types_team_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.feature_types
    ADD CONSTRAINT feature_types_team_id_foreign FOREIGN KEY (team_id) REFERENCES beacon.teams(id) ON DELETE CASCADE;


--
-- Name: invites invites_organization_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.invites
    ADD CONSTRAINT invites_organization_id_foreign FOREIGN KEY (organization_id) REFERENCES beacon.organizations(id) ON DELETE CASCADE;


--
-- Name: invites invites_team_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.invites
    ADD CONSTRAINT invites_team_id_foreign FOREIGN KEY (team_id) REFERENCES beacon.teams(id) ON DELETE CASCADE;


--
-- Name: invites invites_user_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.invites
    ADD CONSTRAINT invites_user_id_foreign FOREIGN KEY (user_id) REFERENCES beacon.users(id) ON DELETE CASCADE;


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES beacon.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES beacon.roles(id) ON DELETE CASCADE;


--
-- Name: organization_user organization_user_organization_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.organization_user
    ADD CONSTRAINT organization_user_organization_id_foreign FOREIGN KEY (organization_id) REFERENCES beacon.organizations(id) ON DELETE CASCADE;


--
-- Name: policies policies_team_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.policies
    ADD CONSTRAINT policies_team_id_foreign FOREIGN KEY (team_id) REFERENCES beacon.teams(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES beacon.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES beacon.roles(id) ON DELETE CASCADE;


--
-- Name: stripe_prices stripe_prices_stripe_product_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.stripe_prices
    ADD CONSTRAINT stripe_prices_stripe_product_id_foreign FOREIGN KEY (stripe_product_id) REFERENCES beacon.stripe_products(id) ON DELETE SET NULL;


--
-- Name: stripe_products stripe_products_stripe_base_price_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.stripe_products
    ADD CONSTRAINT stripe_products_stripe_base_price_id_foreign FOREIGN KEY (stripe_base_price_id) REFERENCES beacon.stripe_prices(id) ON DELETE SET NULL;


--
-- Name: stripe_products stripe_products_stripe_metered_price_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.stripe_products
    ADD CONSTRAINT stripe_products_stripe_metered_price_id_foreign FOREIGN KEY (stripe_metered_price_id) REFERENCES beacon.stripe_prices(id) ON DELETE SET NULL;


--
-- Name: subscriptions subscriptions_organization_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.subscriptions
    ADD CONSTRAINT subscriptions_organization_id_foreign FOREIGN KEY (organization_id) REFERENCES beacon.organizations(id) ON DELETE CASCADE;


--
-- Name: tags tags_team_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.tags
    ADD CONSTRAINT tags_team_id_foreign FOREIGN KEY (team_id) REFERENCES beacon.teams(id) ON DELETE CASCADE;


--
-- Name: team_user team_user_team_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.team_user
    ADD CONSTRAINT team_user_team_id_foreign FOREIGN KEY (team_id) REFERENCES beacon.teams(id) ON DELETE CASCADE;


--
-- Name: team_user team_user_user_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.team_user
    ADD CONSTRAINT team_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES beacon.users(id) ON DELETE CASCADE;


--
-- Name: teams teams_organization_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.teams
    ADD CONSTRAINT teams_organization_id_foreign FOREIGN KEY (organization_id) REFERENCES beacon.organizations(id) ON DELETE CASCADE;


--
-- Name: telescope_entries_tags telescope_entries_tags_entry_uuid_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.telescope_entries_tags
    ADD CONSTRAINT telescope_entries_tags_entry_uuid_foreign FOREIGN KEY (entry_uuid) REFERENCES beacon.telescope_entries(uuid) ON DELETE CASCADE;


--
-- Name: organization_user tenant_user_user_id_foreign; Type: FK CONSTRAINT; Schema: beacon; Owner: -
--

ALTER TABLE ONLY beacon.organization_user
    ADD CONSTRAINT tenant_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES beacon.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

--
-- PostgreSQL database dump
--

-- Dumped from database version 17.0 (Laravel Herd)
-- Dumped by pg_dump version 17.5

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: beacon; Owner: -
--

COPY beacon.migrations (id, migration, batch) FROM stdin;
1	0000_00_00_000000_ensure_schema	1
2	0001_01_01_000000_create_users_table	1
3	0001_01_01_000001_create_cache_table	1
4	0001_01_01_000002_create_jobs_table	1
5	2024_12_25_024505_create_tenants_table	1
6	2024_12_26_030307_create_feature_types_table	1
7	2024_12_26_030428_create_feature_flags_table	1
8	2024_12_26_030630_create_tags_table	1
9	2024_12_26_030810_create_applications_table	1
10	2024_12_26_030905_create_environments_table	1
11	2024_12_26_030951_create_policies_table	1
12	2024_12_26_031101_create_application_feature_flag_table	1
13	2024_12_26_031336_create_feature_flag_tag_table	1
14	2024_12_26_031511_create_environment_feature_flag_table	1
15	2024_12_26_031628_create_feature_flag_policy	1
16	2024_12_28_125113_add_icon_to_feature_types	1
17	2024_12_29_031515_set_color_to_nullable_on_environments	1
18	2024_12_30_045012_create_personal_access_tokens_table	1
19	2024_12_30_090708_create_tenant_user_table	1
20	2025_01_13_091255_create_feature_flag_statuses_table	1
21	2025_01_13_091829_rename_feature_flag_policy_to_feature_flag_status_policy	1
22	2025_03_09_003746_add_policy_to_feature_flag_statuses_table	1
23	2025_03_09_005146_remove_feature_flag_status_policy_table	1
24	2025_03_10_014614_add_status_to_feature_flags_table	1
25	2025_03_14_043045_add_color_icon_to_teams_table	1
26	2025_03_14_060352_create_permission_tables	1
27	2025_03_15_033203_add_avatar_url_to_users_table	1
28	2025_03_16_013254_add_slug_to_teams_table	1
29	2025_03_16_113734_add_two_factor_columns_to_users_table	1
30	2025_03_17_065342_rename_personal_access_tokens_table	1
31	2025_03_17_085101_create_invites_table	1
32	2025_03_18_234030_add_theme_to_users_table	1
33	2025_03_20_083754_add_status_to_users_table	1
34	2025_03_21_005354_add_last_seen_at_to_environments_table	1
35	2025_03_21_104403_create_tenants_table	1
36	2025_03_21_104808_add_tenant_to_teams_table	1
37	2025_03_21_115632_add_name_to_tenants_table	1
38	2025_03_21_133627_update_team_unique_idx_on_teams_table	1
39	2025_03_21_144112_add_slug_to_tenants_table	1
40	2025_03_22_062905_rename_tenants_to_organizations	1
41	2025_04_05_113127_remove_slugs	1
42	2025_04_09_092757_add_unique_index_to_invites	1
43	2025_04_09_155702_create_feature_flag_usages_table	1
44	2025_05_06_085001_create_customer_columns	1
45	2025_05_06_085002_create_subscriptions_table	1
46	2025_05_06_085003_create_subscription_items_table	1
47	2025_05_06_094007_create_stripe_tables	1
48	2025_05_22_051244_create_activity_log_table	1
49	2025_05_22_051245_add_event_column_to_activity_log_table	1
50	2025_05_22_051246_add_batch_uuid_column_to_activity_log_table	1
51	2025_06_13_103608_create_telescope_entries_table	1
52	2025_06_28_064207_add_rollout_to_feature_flag_statuses_table	1
53	2025_07_01_000000_add_variants_to_feature_flag_statuses_table	1
54	2025_07_01_014600_add_feature_flag_name_column_to_feature_flag_usages_table	1
55	2025_07_02_000000_add_variant_stickiness_to_feature_flag_statuses_table	1
56	2025_07_04_074431_add_token_value_to_access_tokens	1
57	2025_07_04_094631_add_is_default_to_feature_types_table	1
58	2025_07_05_081503_change_feature_flag_unique_for_feature_flags_table	1
59	2025_07_11_052027_make_unique_by_team	1
60	2025_07_12_020511_add_onboarded_at_to_organizations_table	1
61	2025_07_25_064426_fix_unique_name_index_on_policies_table	2
62	2025_09_21_002405_last_seen_at_nullable_in_applications	2
63	2025_09_23_080510_unique_email_team_organization_index_on_invites	2
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: beacon; Owner: -
--

SELECT pg_catalog.setval('beacon.migrations_id_seq', 63, true);


--
-- PostgreSQL database dump complete
--

