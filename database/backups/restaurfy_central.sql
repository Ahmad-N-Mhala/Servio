--
-- PostgreSQL database dump
--

\restrict xqbCqa9xfom9wD1ZRSfvk9rA8gzaeoDfSV6Xmm5bNQRHfff35ziMuQba9MumQdL

-- Dumped from database version 14.20 (Homebrew)
-- Dumped by pg_dump version 14.20 (Homebrew)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

ALTER TABLE ONLY public.tenants DROP CONSTRAINT tenants_plan_id_foreign;
ALTER TABLE ONLY public.domains DROP CONSTRAINT domains_tenant_id_foreign;
DROP INDEX public.sessions_user_id_index;
DROP INDEX public.sessions_last_activity_index;
ALTER TABLE ONLY public.tenants DROP CONSTRAINT tenants_pkey;
ALTER TABLE ONLY public.tenants DROP CONSTRAINT tenants_identifier_unique;
ALTER TABLE ONLY public.sessions DROP CONSTRAINT sessions_pkey;
ALTER TABLE ONLY public.plans DROP CONSTRAINT plans_slug_unique;
ALTER TABLE ONLY public.plans DROP CONSTRAINT plans_pkey;
ALTER TABLE ONLY public.migrations DROP CONSTRAINT migrations_pkey;
ALTER TABLE ONLY public.domains DROP CONSTRAINT domains_pkey;
ALTER TABLE ONLY public.domains DROP CONSTRAINT domains_domain_unique;
ALTER TABLE public.plans ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.migrations ALTER COLUMN id DROP DEFAULT;
ALTER TABLE public.domains ALTER COLUMN id DROP DEFAULT;
DROP TABLE public.tenants;
DROP TABLE public.sessions;
DROP SEQUENCE public.plans_id_seq;
DROP TABLE public.plans;
DROP SEQUENCE public.migrations_id_seq;
DROP TABLE public.migrations;
DROP SEQUENCE public.domains_id_seq;
DROP TABLE public.domains;
SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: domains; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.domains (
    id bigint NOT NULL,
    domain character varying(191) NOT NULL,
    tenant_id character varying(191) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.domains OWNER TO ahmadmhala;

--
-- Name: domains_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.domains_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.domains_id_seq OWNER TO ahmadmhala;

--
-- Name: domains_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.domains_id_seq OWNED BY public.domains.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(191) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO ahmadmhala;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.migrations_id_seq OWNER TO ahmadmhala;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: plans; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.plans (
    id bigint NOT NULL,
    name character varying(191) NOT NULL,
    slug character varying(191) NOT NULL,
    price_monthly numeric(10,2) NOT NULL,
    price_yearly numeric(10,2) NOT NULL,
    currency character varying(3) DEFAULT 'AED'::character varying NOT NULL,
    features json,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.plans OWNER TO ahmadmhala;

--
-- Name: plans_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.plans_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.plans_id_seq OWNER TO ahmadmhala;

--
-- Name: plans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.plans_id_seq OWNED BY public.plans.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.sessions (
    id character varying(191) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO ahmadmhala;

--
-- Name: tenants; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.tenants (
    id character varying(191) NOT NULL,
    identifier character varying(191) NOT NULL,
    plan_id bigint,
    subscription_status character varying(191) DEFAULT 'trial'::character varying NOT NULL,
    subscription_ends_at timestamp(0) without time zone,
    data json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.tenants OWNER TO ahmadmhala;

--
-- Name: domains id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.domains ALTER COLUMN id SET DEFAULT nextval('public.domains_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: plans id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.plans ALTER COLUMN id SET DEFAULT nextval('public.plans_id_seq'::regclass);


--
-- Data for Name: domains; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.domains (id, domain, tenant_id, created_at, updated_at) FROM stdin;
1	ahmadtest.localhost	ahmadtest	2025-12-10 11:46:09	2025-12-10 11:46:09
2	demo.localhost	demo	2025-12-12 09:18:22	2025-12-12 09:18:22
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2024_01_01_000000_create_plans_table	1
2	2024_01_01_000001_create_tenants_table	1
3	2025_11_22_124629_create_sessions_table	1
4	2025_11_22_125559_create_domains_table	1
\.


--
-- Data for Name: plans; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.plans (id, name, slug, price_monthly, price_yearly, currency, features, is_active, created_at, updated_at) FROM stdin;
1	Basic	basic	99.00	990.00	AED	["1 restaurant","Up to 5 staff members","Basic menu management","Order tracking","Email support"]	t	2025-12-10 11:46:09	2025-12-12 09:17:51
3	Pro	pro	299.00	2990.00	AED	["3 restaurants","Unlimited staff members","Advanced menu management","Real-time order tracking","Analytics & reports","Priority support"]	t	2025-12-12 09:17:51	2025-12-12 09:17:51
4	Enterprise	enterprise	799.00	7990.00	AED	["Unlimited restaurants","Unlimited staff members","Advanced menu management","Real-time order tracking","Advanced analytics & reports","API access","Custom integrations","Dedicated support"]	t	2025-12-12 09:17:51	2025-12-12 09:17:51
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.


--
-- Data for Name: tenants; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.tenants (id, identifier, plan_id, subscription_status, subscription_ends_at, data, created_at, updated_at) FROM stdin;
ahmadtest	ahmadtest	1	trial	\N	{"updated_at":"2025-12-10 11:46:09","created_at":"2025-12-10 11:46:09","tenancy_db_name":"restaurfy_tenant_ahmadtest"}	2025-12-10 11:46:09	2025-12-10 11:46:09
demo	demo	3	active	2026-12-12 09:18:22	[]	2025-12-12 09:18:22	2025-12-12 09:18:22
\.


--
-- Name: domains_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.domains_id_seq', 2, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.migrations_id_seq', 4, true);


--
-- Name: plans_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.plans_id_seq', 4, true);


--
-- Name: domains domains_domain_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.domains
    ADD CONSTRAINT domains_domain_unique UNIQUE (domain);


--
-- Name: domains domains_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.domains
    ADD CONSTRAINT domains_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: plans plans_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.plans
    ADD CONSTRAINT plans_pkey PRIMARY KEY (id);


--
-- Name: plans plans_slug_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.plans
    ADD CONSTRAINT plans_slug_unique UNIQUE (slug);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: tenants tenants_identifier_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_identifier_unique UNIQUE (identifier);


--
-- Name: tenants tenants_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_pkey PRIMARY KEY (id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: domains domains_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.domains
    ADD CONSTRAINT domains_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: tenants tenants_plan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_plan_id_foreign FOREIGN KEY (plan_id) REFERENCES public.plans(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict xqbCqa9xfom9wD1ZRSfvk9rA8gzaeoDfSV6Xmm5bNQRHfff35ziMuQba9MumQdL

