--
-- PostgreSQL database dump
--

\restrict 1kLH7ZsbgjOrnPKIbxkAdiimA76UGomB40puSIo9q0DD8X1zNZk9sOB8gZXfmM9

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

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: communication_bundles; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.communication_bundles (
    id bigint NOT NULL,
    name character varying(191) NOT NULL,
    type character varying(191) NOT NULL,
    quantity integer NOT NULL,
    price numeric(10,2) NOT NULL,
    currency character varying(3) DEFAULT 'AED'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.communication_bundles OWNER TO ahmadmhala;

--
-- Name: communication_bundles_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.communication_bundles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.communication_bundles_id_seq OWNER TO ahmadmhala;

--
-- Name: communication_bundles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.communication_bundles_id_seq OWNED BY public.communication_bundles.id;


--
-- Name: communication_logs; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.communication_logs (
    id bigint NOT NULL,
    type character varying(191) NOT NULL,
    recipient character varying(191) NOT NULL,
    message text,
    status character varying(191) DEFAULT 'pending'::character varying NOT NULL,
    cost numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    error_message text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    communication_template_id bigint
);


ALTER TABLE public.communication_logs OWNER TO ahmadmhala;

--
-- Name: communication_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.communication_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.communication_logs_id_seq OWNER TO ahmadmhala;

--
-- Name: communication_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.communication_logs_id_seq OWNED BY public.communication_logs.id;


--
-- Name: communication_templates; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.communication_templates (
    id bigint NOT NULL,
    restaurant_id bigint NOT NULL,
    name character varying(191) NOT NULL,
    trigger_event character varying(191) NOT NULL,
    subject character varying(191),
    content text NOT NULL,
    conditions json,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    channels json,
    timing_type character varying(191) DEFAULT 'immediately'::character varying NOT NULL,
    timing_days integer DEFAULT 0 NOT NULL,
    timing_time time(0) without time zone DEFAULT '12:00:00'::time without time zone NOT NULL
);


ALTER TABLE public.communication_templates OWNER TO ahmadmhala;

--
-- Name: communication_templates_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.communication_templates_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.communication_templates_id_seq OWNER TO ahmadmhala;

--
-- Name: communication_templates_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.communication_templates_id_seq OWNED BY public.communication_templates.id;


--
-- Name: delivery_integrations; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.delivery_integrations (
    id bigint NOT NULL,
    restaurant_id bigint NOT NULL,
    provider character varying(191) NOT NULL,
    api_key character varying(191),
    api_secret character varying(191),
    store_id character varying(191),
    webhook_secret character varying(191),
    settings json,
    is_enabled boolean DEFAULT false NOT NULL,
    auto_accept_orders boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.delivery_integrations OWNER TO ahmadmhala;

--
-- Name: delivery_integrations_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.delivery_integrations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.delivery_integrations_id_seq OWNER TO ahmadmhala;

--
-- Name: delivery_integrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.delivery_integrations_id_seq OWNED BY public.delivery_integrations.id;


--
-- Name: communication_bundles id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.communication_bundles ALTER COLUMN id SET DEFAULT nextval('public.communication_bundles_id_seq'::regclass);


--
-- Name: communication_logs id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.communication_logs ALTER COLUMN id SET DEFAULT nextval('public.communication_logs_id_seq'::regclass);


--
-- Name: communication_templates id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.communication_templates ALTER COLUMN id SET DEFAULT nextval('public.communication_templates_id_seq'::regclass);


--
-- Name: delivery_integrations id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.delivery_integrations ALTER COLUMN id SET DEFAULT nextval('public.delivery_integrations_id_seq'::regclass);


--
-- Name: communication_bundles communication_bundles_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.communication_bundles
    ADD CONSTRAINT communication_bundles_pkey PRIMARY KEY (id);


--
-- Name: communication_logs communication_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.communication_logs
    ADD CONSTRAINT communication_logs_pkey PRIMARY KEY (id);


--
-- Name: communication_templates communication_templates_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.communication_templates
    ADD CONSTRAINT communication_templates_pkey PRIMARY KEY (id);


--
-- Name: delivery_integrations delivery_integrations_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.delivery_integrations
    ADD CONSTRAINT delivery_integrations_pkey PRIMARY KEY (id);


--
-- Name: delivery_integrations delivery_integrations_restaurant_id_provider_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.delivery_integrations
    ADD CONSTRAINT delivery_integrations_restaurant_id_provider_unique UNIQUE (restaurant_id, provider);


--
-- Name: communication_logs communication_logs_communication_template_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.communication_logs
    ADD CONSTRAINT communication_logs_communication_template_id_foreign FOREIGN KEY (communication_template_id) REFERENCES public.communication_templates(id) ON DELETE SET NULL;


--
-- Name: communication_templates communication_templates_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.communication_templates
    ADD CONSTRAINT communication_templates_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


--
-- Name: delivery_integrations delivery_integrations_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.delivery_integrations
    ADD CONSTRAINT delivery_integrations_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict 1kLH7ZsbgjOrnPKIbxkAdiimA76UGomB40puSIo9q0DD8X1zNZk9sOB8gZXfmM9

