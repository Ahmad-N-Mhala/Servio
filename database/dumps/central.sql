--
-- PostgreSQL database dump
--

\restrict 8mAG9KqiEybU6uDYrpz9IF6D6UTh9L5xTdaYG93RnET7shA2PKYx3SHe4vOL6C5

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
-- Name: customers; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.customers (
    id bigint NOT NULL,
    restaurant_id bigint NOT NULL,
    phone character varying(191) NOT NULL,
    name character varying(191),
    email character varying(191),
    birthday date,
    preferences json,
    total_orders integer DEFAULT 0 NOT NULL,
    total_spent numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    loyalty_tier character varying(191) DEFAULT 'bronze'::character varying NOT NULL,
    last_order_at timestamp(0) without time zone,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.customers OWNER TO ahmadmhala;

--
-- Name: customers_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.customers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.customers_id_seq OWNER TO ahmadmhala;

--
-- Name: customers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.customers_id_seq OWNED BY public.customers.id;


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
-- Name: earning_methods; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.earning_methods (
    id bigint NOT NULL,
    restaurant_id bigint NOT NULL,
    name json NOT NULL,
    description character varying(191),
    type character varying(191) NOT NULL,
    points integer NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.earning_methods OWNER TO ahmadmhala;

--
-- Name: earning_methods_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.earning_methods_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.earning_methods_id_seq OWNER TO ahmadmhala;

--
-- Name: earning_methods_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.earning_methods_id_seq OWNED BY public.earning_methods.id;


--
-- Name: loyalty_points; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.loyalty_points (
    id bigint NOT NULL,
    customer_id bigint NOT NULL,
    balance integer DEFAULT 0 NOT NULL,
    total_earned integer DEFAULT 0 NOT NULL,
    total_redeemed integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.loyalty_points OWNER TO ahmadmhala;

--
-- Name: loyalty_points_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.loyalty_points_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.loyalty_points_id_seq OWNER TO ahmadmhala;

--
-- Name: loyalty_points_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.loyalty_points_id_seq OWNED BY public.loyalty_points.id;


--
-- Name: menu_categories; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.menu_categories (
    id bigint NOT NULL,
    restaurant_id bigint NOT NULL,
    name json NOT NULL,
    description text,
    sort_order integer DEFAULT 0 NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.menu_categories OWNER TO ahmadmhala;

--
-- Name: menu_categories_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.menu_categories_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.menu_categories_id_seq OWNER TO ahmadmhala;

--
-- Name: menu_categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.menu_categories_id_seq OWNED BY public.menu_categories.id;


--
-- Name: menu_items; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.menu_items (
    id bigint NOT NULL,
    restaurant_id bigint NOT NULL,
    menu_category_id bigint NOT NULL,
    name json NOT NULL,
    description text,
    price numeric(10,2) NOT NULL,
    currency character varying(3) DEFAULT 'AED'::character varying NOT NULL,
    image character varying(191),
    is_available boolean DEFAULT true NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    allergens json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.menu_items OWNER TO ahmadmhala;

--
-- Name: menu_items_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.menu_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.menu_items_id_seq OWNER TO ahmadmhala;

--
-- Name: menu_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.menu_items_id_seq OWNED BY public.menu_items.id;


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
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(191) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO ahmadmhala;

--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(191) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO ahmadmhala;

--
-- Name: order_items; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.order_items (
    id bigint NOT NULL,
    order_id bigint NOT NULL,
    menu_item_id bigint NOT NULL,
    quantity integer NOT NULL,
    unit_price numeric(10,2) NOT NULL,
    total_price numeric(10,2) NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.order_items OWNER TO ahmadmhala;

--
-- Name: order_items_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.order_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.order_items_id_seq OWNER TO ahmadmhala;

--
-- Name: order_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.order_items_id_seq OWNED BY public.order_items.id;


--
-- Name: orders; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.orders (
    id bigint NOT NULL,
    restaurant_id bigint NOT NULL,
    order_number character varying(191) NOT NULL,
    status character varying(191) DEFAULT 'pending'::character varying NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    tax numeric(10,2) DEFAULT '0'::numeric NOT NULL,
    total numeric(10,2) NOT NULL,
    currency character varying(3) DEFAULT 'AED'::character varying NOT NULL,
    customer_id bigint,
    customer_name character varying(191),
    customer_phone character varying(191),
    points_earned integer DEFAULT 0 NOT NULL,
    points_redeemed integer DEFAULT 0 NOT NULL,
    notes text,
    completed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.orders OWNER TO ahmadmhala;

--
-- Name: orders_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.orders_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.orders_id_seq OWNER TO ahmadmhala;

--
-- Name: orders_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.orders_id_seq OWNED BY public.orders.id;


--
-- Name: payments; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.payments (
    id bigint NOT NULL,
    subscription_id bigint,
    stripe_payment_intent_id character varying(191),
    amount numeric(10,2) NOT NULL,
    currency character varying(3) DEFAULT 'AED'::character varying NOT NULL,
    status character varying(191) NOT NULL,
    payment_method character varying(191),
    metadata json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.payments OWNER TO ahmadmhala;

--
-- Name: payments_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.payments_id_seq OWNER TO ahmadmhala;

--
-- Name: payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.payments_id_seq OWNED BY public.payments.id;


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(191) NOT NULL,
    guard_name character varying(191) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO ahmadmhala;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.permissions_id_seq OWNER TO ahmadmhala;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


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
-- Name: point_transactions; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.point_transactions (
    id bigint NOT NULL,
    customer_id bigint NOT NULL,
    order_id bigint,
    reward_redemption_id bigint,
    type character varying(191) NOT NULL,
    points integer NOT NULL,
    description text,
    balance_after integer DEFAULT 0 NOT NULL,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.point_transactions OWNER TO ahmadmhala;

--
-- Name: point_transactions_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.point_transactions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.point_transactions_id_seq OWNER TO ahmadmhala;

--
-- Name: point_transactions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.point_transactions_id_seq OWNED BY public.point_transactions.id;


--
-- Name: restaurants; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.restaurants (
    id bigint NOT NULL,
    name character varying(191) NOT NULL,
    slug character varying(191) NOT NULL,
    description text,
    phone character varying(191),
    email character varying(191),
    address text,
    city character varying(191),
    country character varying(191),
    currency character varying(3) DEFAULT 'AED'::character varying NOT NULL,
    locale character varying(5) DEFAULT 'en'::character varying NOT NULL,
    settings json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.restaurants OWNER TO ahmadmhala;

--
-- Name: restaurants_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.restaurants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.restaurants_id_seq OWNER TO ahmadmhala;

--
-- Name: restaurants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.restaurants_id_seq OWNED BY public.restaurants.id;


--
-- Name: reward_redemptions; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.reward_redemptions (
    id bigint NOT NULL,
    customer_id bigint NOT NULL,
    reward_id bigint NOT NULL,
    order_id bigint,
    points_used integer NOT NULL,
    status character varying(191) DEFAULT 'pending'::character varying NOT NULL,
    code character varying(191),
    used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    metadata json,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.reward_redemptions OWNER TO ahmadmhala;

--
-- Name: reward_redemptions_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.reward_redemptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.reward_redemptions_id_seq OWNER TO ahmadmhala;

--
-- Name: reward_redemptions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.reward_redemptions_id_seq OWNED BY public.reward_redemptions.id;


--
-- Name: rewards; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.rewards (
    id bigint NOT NULL,
    restaurant_id bigint NOT NULL,
    name json NOT NULL,
    description text,
    points_required integer NOT NULL,
    reward_type character varying(191) NOT NULL,
    discount_value numeric(10,2),
    menu_item_id bigint,
    max_redemptions integer,
    redemptions_count integer DEFAULT 0 NOT NULL,
    valid_from date,
    valid_until date,
    is_active boolean DEFAULT true NOT NULL,
    sort_order integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.rewards OWNER TO ahmadmhala;

--
-- Name: rewards_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.rewards_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.rewards_id_seq OWNER TO ahmadmhala;

--
-- Name: rewards_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.rewards_id_seq OWNED BY public.rewards.id;


--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO ahmadmhala;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(191) NOT NULL,
    guard_name character varying(191) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO ahmadmhala;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.roles_id_seq OWNER TO ahmadmhala;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


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
-- Name: staff; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.staff (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    restaurant_id bigint NOT NULL,
    role character varying(191) DEFAULT 'waiter'::character varying NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    invited_at timestamp(0) without time zone,
    joined_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.staff OWNER TO ahmadmhala;

--
-- Name: staff_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.staff_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.staff_id_seq OWNER TO ahmadmhala;

--
-- Name: staff_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.staff_id_seq OWNED BY public.staff.id;


--
-- Name: subscriptions; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.subscriptions (
    id bigint NOT NULL,
    stripe_subscription_id character varying(191),
    stripe_customer_id character varying(191),
    status character varying(191) NOT NULL,
    plan_slug character varying(191) NOT NULL,
    billing_cycle character varying(191) DEFAULT 'monthly'::character varying NOT NULL,
    starts_at timestamp(0) without time zone NOT NULL,
    ends_at timestamp(0) without time zone,
    canceled_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.subscriptions OWNER TO ahmadmhala;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.subscriptions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.subscriptions_id_seq OWNER TO ahmadmhala;

--
-- Name: subscriptions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.subscriptions_id_seq OWNED BY public.subscriptions.id;


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
-- Name: users; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(191) NOT NULL,
    email character varying(191) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(191) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO ahmadmhala;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.users_id_seq OWNER TO ahmadmhala;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: customers id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.customers ALTER COLUMN id SET DEFAULT nextval('public.customers_id_seq'::regclass);


--
-- Name: domains id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.domains ALTER COLUMN id SET DEFAULT nextval('public.domains_id_seq'::regclass);


--
-- Name: earning_methods id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.earning_methods ALTER COLUMN id SET DEFAULT nextval('public.earning_methods_id_seq'::regclass);


--
-- Name: loyalty_points id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.loyalty_points ALTER COLUMN id SET DEFAULT nextval('public.loyalty_points_id_seq'::regclass);


--
-- Name: menu_categories id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.menu_categories ALTER COLUMN id SET DEFAULT nextval('public.menu_categories_id_seq'::regclass);


--
-- Name: menu_items id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.menu_items ALTER COLUMN id SET DEFAULT nextval('public.menu_items_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: order_items id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.order_items ALTER COLUMN id SET DEFAULT nextval('public.order_items_id_seq'::regclass);


--
-- Name: orders id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.orders ALTER COLUMN id SET DEFAULT nextval('public.orders_id_seq'::regclass);


--
-- Name: payments id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.payments ALTER COLUMN id SET DEFAULT nextval('public.payments_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: plans id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.plans ALTER COLUMN id SET DEFAULT nextval('public.plans_id_seq'::regclass);


--
-- Name: point_transactions id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.point_transactions ALTER COLUMN id SET DEFAULT nextval('public.point_transactions_id_seq'::regclass);


--
-- Name: restaurants id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.restaurants ALTER COLUMN id SET DEFAULT nextval('public.restaurants_id_seq'::regclass);


--
-- Name: reward_redemptions id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.reward_redemptions ALTER COLUMN id SET DEFAULT nextval('public.reward_redemptions_id_seq'::regclass);


--
-- Name: rewards id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.rewards ALTER COLUMN id SET DEFAULT nextval('public.rewards_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: staff id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.staff ALTER COLUMN id SET DEFAULT nextval('public.staff_id_seq'::regclass);


--
-- Name: subscriptions id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.subscriptions ALTER COLUMN id SET DEFAULT nextval('public.subscriptions_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: customers; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.customers (id, restaurant_id, phone, name, email, birthday, preferences, total_orders, total_spent, loyalty_tier, last_order_at, is_active, created_at, updated_at) FROM stdin;
1	1	+971-50-0000001	Ahmed Ali	ahmed.ali@example.com	\N	\N	0	0.00	gold	\N	t	2025-12-04 06:10:43	2025-12-04 06:10:43
2	1	+971-50-0000002	Fatima Hassan	fatima.hassan@example.com	\N	\N	0	0.00	silver	\N	t	2025-12-04 06:10:43	2025-12-04 06:10:43
3	1	+971-50-0000003	Mohammed Khalid	mohammed.khalid@example.com	\N	\N	0	0.00	bronze	\N	t	2025-12-04 06:10:43	2025-12-04 06:10:43
4	1	+971-50-0000004	Sara Ahmed	sara.ahmed@example.com	\N	\N	0	0.00	gold	\N	t	2025-12-04 06:10:43	2025-12-04 06:10:43
5	1	+971-50-0000005	Omar Abdullah	omar.abdullah@example.com	\N	\N	0	0.00	bronze	\N	t	2025-12-04 06:10:43	2025-12-04 06:10:43
6	1	+971-50-0000006	Layla Ibrahim	layla.ibrahim@example.com	\N	\N	0	0.00	gold	\N	t	2025-12-04 06:10:43	2025-12-04 06:10:43
7	1	+971-50-0000007	Youssef Mahmoud	youssef.mahmoud@example.com	\N	\N	0	0.00	gold	\N	t	2025-12-04 06:10:43	2025-12-04 06:10:43
8	1	+971-50-0000008	Noor Saleh	noor.saleh@example.com	\N	\N	0	0.00	bronze	\N	t	2025-12-04 06:10:43	2025-12-04 06:10:43
9	1	+971-50-0000009	Khaled Rashid	khaled.rashid@example.com	\N	\N	0	0.00	silver	\N	t	2025-12-04 06:10:43	2025-12-04 06:10:43
10	1	+971-50-0000010	Maryam Yousef	maryam.yousef@example.com	\N	\N	0	0.00	gold	\N	t	2025-12-04 06:10:43	2025-12-04 06:10:43
\.


--
-- Data for Name: domains; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.domains (id, domain, tenant_id, created_at, updated_at) FROM stdin;
2	demo.localhost	demo	2025-12-04 06:08:03	2025-12-04 06:08:03
3	testrestaurant.localhost	b79581da-ce6b-40c4-9fc0-80a77532f8fc	2025-12-04 06:17:03	2025-12-04 06:17:03
4	ahmadtest.localhost	72bd94a7-1fcb-4802-8fe3-f252d2b08a6c	2025-12-06 06:21:42	2025-12-06 06:21:42
\.


--
-- Data for Name: earning_methods; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.earning_methods (id, restaurant_id, name, description, type, points, is_active, created_at, updated_at) FROM stdin;
1	1	{"en":"Test Method"}	\N	order_total	1	t	2025-12-06 08:06:00	2025-12-06 08:06:00
\.


--
-- Data for Name: loyalty_points; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.loyalty_points (id, customer_id, balance, total_earned, total_redeemed, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: menu_categories; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.menu_categories (id, restaurant_id, name, description, sort_order, is_active, created_at, updated_at) FROM stdin;
1	1	{"en":"Appetizers","ar":"المقبلات"}	\N	1	t	2025-12-04 06:10:43	2025-12-04 06:10:43
2	1	{"en":"Main Courses","ar":"الأطباق الرئيسية"}	\N	2	t	2025-12-04 06:10:43	2025-12-04 06:10:43
3	1	{"en":"Desserts","ar":"الحلويات"}	\N	3	t	2025-12-04 06:10:43	2025-12-04 06:10:43
4	1	{"en":"Beverages","ar":"المشروبات"}	\N	4	t	2025-12-04 06:10:43	2025-12-04 06:10:43
\.


--
-- Data for Name: menu_items; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.menu_items (id, restaurant_id, menu_category_id, name, description, price, currency, image, is_available, sort_order, allergens, created_at, updated_at) FROM stdin;
1	1	1	{"en":"Hummus","ar":"حمص"}	Delicious Hummus	25.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
2	1	1	{"en":"Falafel","ar":"فلافل"}	Delicious Falafel	20.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
3	1	1	{"en":"Spring Rolls","ar":"سبرينج رول"}	Delicious Spring Rolls	30.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
4	1	2	{"en":"Grilled Chicken","ar":"دجاج مشوي"}	Delicious Grilled Chicken	65.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
5	1	2	{"en":"Beef Burger","ar":"برجر لحم"}	Delicious Beef Burger	55.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
6	1	2	{"en":"Margherita Pizza","ar":"بيتزا مارجريتا"}	Delicious Margherita Pizza	50.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
7	1	2	{"en":"Pasta Carbonara","ar":"باستا كاربونارا"}	Delicious Pasta Carbonara	60.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
8	1	2	{"en":"Fish & Chips","ar":"سمك وبطاطس"}	Delicious Fish & Chips	70.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
9	1	3	{"en":"Chocolate Cake","ar":"كيك شوكولاتة"}	Delicious Chocolate Cake	35.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
10	1	3	{"en":"Ice Cream","ar":"آيس كريم"}	Delicious Ice Cream	25.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
11	1	3	{"en":"Tiramisu","ar":"تيراميسو"}	Delicious Tiramisu	40.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
12	1	4	{"en":"Fresh Orange Juice","ar":"عصير برتقال طازج"}	Delicious Fresh Orange Juice	20.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
13	1	4	{"en":"Coffee","ar":"قهوة"}	Delicious Coffee	15.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
14	1	4	{"en":"Soft Drink","ar":"مشروب غازي"}	Delicious Soft Drink	10.00	AED	\N	t	0	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2024_01_01_000000_create_plans_table	1
2	2024_01_01_000001_create_tenants_table	1
3	2025_11_22_124629_create_sessions_table	1
4	2025_11_22_125559_create_domains_table	1
5	2024_01_01_100000_create_restaurants_table	2
6	2024_01_01_100001_create_users_table	2
7	2024_01_01_100002_create_customers_table	2
8	2024_01_01_100003_create_staff_table	2
9	2024_01_01_100004_create_menu_categories_table	2
10	2024_01_01_100005_create_menu_items_table	2
11	2024_01_01_100006_create_orders_table	2
12	2024_01_01_100007_create_order_items_table	2
13	2024_01_01_100008_create_subscriptions_table	2
14	2024_01_01_100009_create_payments_table	2
15	2024_01_01_100010_create_permission_tables	2
16	2024_01_01_100012_create_loyalty_points_table	2
17	2024_01_01_100013_create_rewards_table	2
18	2024_01_01_100014_create_reward_redemptions_table	2
19	2024_01_01_100015_create_point_transactions_table	2
20	2025_12_06_074836_create_earning_methods_table	3
\.


--
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
\.


--
-- Data for Name: order_items; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.order_items (id, order_id, menu_item_id, quantity, unit_price, total_price, notes, created_at, updated_at) FROM stdin;
1	1	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
2	1	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
3	1	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
4	1	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
5	1	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
6	2	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
7	2	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
8	2	8	3	70.00	210.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
9	3	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
10	4	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
11	4	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
12	4	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
13	4	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
14	5	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
15	5	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
16	5	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
17	6	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
18	6	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
19	6	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
20	7	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
21	7	8	3	70.00	210.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
22	7	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
23	8	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
24	8	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
25	8	4	2	65.00	130.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
26	8	4	1	65.00	65.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
27	8	3	3	30.00	90.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
28	9	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
29	9	7	3	60.00	180.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
30	9	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
31	10	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
32	10	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
33	11	8	3	70.00	210.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
34	11	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
35	12	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
36	13	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
37	13	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
38	14	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
39	14	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
40	14	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
41	15	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
42	15	8	3	70.00	210.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
43	16	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
44	16	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
45	16	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
46	17	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
47	17	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
48	17	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
49	17	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
50	18	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
51	18	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
52	18	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
53	18	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
54	18	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
55	19	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
56	20	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
57	20	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
58	21	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
59	21	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
60	21	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
61	21	10	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
62	21	8	1	70.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
63	22	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
64	22	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
65	23	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
66	24	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
67	25	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
68	25	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
69	25	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
70	26	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
71	26	8	1	70.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
72	27	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
73	28	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
74	28	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
75	28	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
76	29	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
77	29	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
78	30	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
79	30	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
80	31	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
81	31	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
82	31	4	2	65.00	130.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
83	32	6	2	50.00	100.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
84	32	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
85	33	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
86	33	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
87	34	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
88	34	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
89	34	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
90	35	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
91	35	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
92	36	4	1	65.00	65.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
93	36	4	1	65.00	65.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
94	36	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
95	36	8	3	70.00	210.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
96	37	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
97	37	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
98	37	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
99	37	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
100	37	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
101	38	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
102	38	8	3	70.00	210.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
103	38	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
104	39	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
105	39	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
106	40	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
107	40	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
108	40	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
109	41	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
110	41	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
111	41	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
112	41	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
113	41	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
114	42	6	2	50.00	100.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
115	42	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
116	42	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
117	42	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
118	43	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
119	43	7	3	60.00	180.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
120	44	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
121	44	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
122	44	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
123	44	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
124	45	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
125	45	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
126	45	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
127	45	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
128	46	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
129	46	7	2	60.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
130	46	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
131	46	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
132	47	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
133	48	4	1	65.00	65.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
134	48	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
135	48	11	1	40.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
136	49	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
137	49	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
138	49	7	2	60.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
139	49	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
140	49	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
141	50	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
142	50	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
143	50	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
144	50	7	2	60.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
145	50	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
146	51	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
147	51	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
148	51	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
149	51	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
150	52	7	2	60.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
151	52	4	2	65.00	130.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
152	52	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
153	52	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
154	53	7	3	60.00	180.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
155	54	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
156	55	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
157	55	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
158	55	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
159	55	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
160	55	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
161	56	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
162	56	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
163	57	7	3	60.00	180.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
164	58	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
165	58	8	1	70.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
166	58	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
167	58	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
168	59	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
169	60	9	3	35.00	105.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
170	61	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
171	61	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
172	62	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
173	62	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
174	63	8	1	70.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
175	64	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
176	65	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
177	65	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
178	65	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
179	65	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
180	66	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
181	67	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
182	67	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
183	68	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
184	68	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
185	69	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
186	69	3	3	30.00	90.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
187	70	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
188	70	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
189	70	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
190	71	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
191	71	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
192	71	11	1	40.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
193	71	4	1	65.00	65.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
194	71	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
195	72	6	2	50.00	100.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
196	72	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
197	72	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
198	72	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
199	72	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
200	73	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
201	73	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
202	73	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
203	74	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
204	74	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
205	74	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
206	75	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
207	76	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
208	76	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
209	77	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
210	77	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
211	78	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
212	78	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
213	79	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
214	79	6	2	50.00	100.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
215	79	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
216	79	10	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
217	80	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
218	81	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
219	81	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
220	81	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
221	81	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
222	81	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
223	82	7	3	60.00	180.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
224	83	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
225	83	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
226	83	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
227	84	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
228	84	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
229	84	7	2	60.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
230	84	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
231	84	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
232	85	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
233	85	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
234	85	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
235	85	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
236	85	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
237	86	3	3	30.00	90.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
238	86	9	3	35.00	105.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
239	87	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
240	87	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
241	87	9	3	35.00	105.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
242	87	8	1	70.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
243	87	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
244	88	6	2	50.00	100.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
245	88	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
246	88	8	1	70.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
247	88	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
248	88	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
249	89	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
250	90	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
251	90	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
252	90	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
253	90	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
254	90	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
255	91	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
256	92	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
257	92	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
258	92	11	1	40.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
259	92	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
260	93	4	2	65.00	130.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
261	93	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
262	93	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
263	94	8	3	70.00	210.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
264	94	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
265	94	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
266	95	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
267	95	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
268	95	4	2	65.00	130.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
269	95	8	1	70.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
270	95	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
271	96	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
272	96	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
273	96	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
274	96	10	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
275	97	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
276	97	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
277	97	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
278	98	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
279	98	8	1	70.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
280	99	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
281	99	8	3	70.00	210.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
282	100	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
283	100	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
284	100	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
285	100	6	2	50.00	100.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
286	101	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
287	102	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
288	102	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
289	102	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
290	103	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
291	103	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
292	103	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
293	103	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
294	104	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
295	104	3	3	30.00	90.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
296	104	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
297	105	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
298	105	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
299	106	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
300	106	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
301	107	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
302	107	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
303	107	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
304	108	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
305	108	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
306	108	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
307	109	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
308	109	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
309	110	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
310	110	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
311	110	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
312	110	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
313	110	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
314	111	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
315	112	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
316	112	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
317	112	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
318	112	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
319	112	9	3	35.00	105.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
320	113	8	3	70.00	210.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
321	114	3	3	30.00	90.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
322	114	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
323	114	4	2	65.00	130.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
324	115	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
325	116	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
326	116	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
327	117	4	1	65.00	65.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
328	118	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
329	118	11	1	40.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
330	118	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
331	119	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
332	119	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
333	120	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
334	120	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
335	120	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
336	120	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
337	121	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
338	121	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
339	122	9	3	35.00	105.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
340	123	3	3	30.00	90.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
341	123	7	3	60.00	180.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
342	124	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
343	124	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
344	125	4	1	65.00	65.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
345	125	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
346	125	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
347	125	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
348	126	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
349	126	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
350	126	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
351	126	4	2	65.00	130.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
352	126	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
353	127	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
354	127	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
355	127	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
356	128	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
357	128	7	3	60.00	180.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
358	129	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
359	129	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
360	130	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
361	130	7	2	60.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
362	130	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
363	130	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
364	131	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
365	131	9	3	35.00	105.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
366	131	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
367	132	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
368	133	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
369	133	4	2	65.00	130.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
370	133	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
371	133	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
372	133	10	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
373	134	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
374	134	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
375	135	4	2	65.00	130.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
376	135	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
377	135	11	1	40.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
378	135	6	2	50.00	100.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
379	136	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
380	137	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
381	138	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
382	138	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
383	138	11	1	40.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
384	138	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
385	138	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
386	139	4	3	65.00	195.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
387	139	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
388	140	7	3	60.00	180.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
389	140	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
390	140	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
391	140	3	3	30.00	90.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
392	140	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
393	141	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
394	141	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
395	141	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
396	141	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
397	142	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
398	142	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
399	143	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
400	143	8	2	70.00	140.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
401	143	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
402	143	7	2	60.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
403	143	7	3	60.00	180.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
404	144	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
405	144	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
406	144	9	3	35.00	105.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
407	145	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
408	145	8	1	70.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
409	145	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
410	145	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
411	145	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
412	146	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
413	146	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
414	147	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
415	147	9	3	35.00	105.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
416	147	7	3	60.00	180.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
417	147	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
418	147	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
419	148	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
420	149	4	1	65.00	65.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
421	150	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
422	150	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
423	151	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
424	151	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
425	151	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
426	152	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
427	152	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
428	152	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
429	153	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
430	154	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
431	155	12	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
432	155	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
433	155	2	1	20.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
434	156	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
435	156	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
436	156	14	3	10.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
437	157	4	2	65.00	130.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
438	158	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
439	159	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
440	159	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
441	159	10	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
442	159	1	1	25.00	25.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
443	160	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
444	160	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
445	160	7	2	60.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
446	160	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
447	161	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
448	161	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
449	162	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
450	162	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
451	162	11	2	40.00	80.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
452	162	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
453	162	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
454	163	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
455	164	6	3	50.00	150.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
456	164	2	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
457	164	7	2	60.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
458	164	9	2	35.00	70.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
459	164	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
460	165	4	1	65.00	65.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
461	165	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
462	165	14	1	10.00	10.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
463	165	7	1	60.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
464	166	13	2	15.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
465	167	9	3	35.00	105.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
466	167	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
467	167	5	3	55.00	165.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
468	167	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
469	167	3	1	30.00	30.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
470	168	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
471	169	13	1	15.00	15.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
472	169	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
473	169	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
474	170	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
475	170	7	2	60.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
476	170	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
477	170	11	1	40.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
478	171	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
479	171	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
480	171	14	2	10.00	20.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
481	171	10	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
482	172	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
483	172	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
484	172	11	1	40.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
485	172	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
486	172	11	3	40.00	120.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
487	173	13	3	15.00	45.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
488	173	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
489	174	5	1	55.00	55.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
490	174	10	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
491	174	12	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
492	175	1	3	25.00	75.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
493	175	2	2	20.00	40.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
494	175	6	1	50.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
495	175	12	3	20.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
496	175	3	2	30.00	60.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
497	176	3	3	30.00	90.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
498	176	9	1	35.00	35.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
499	176	5	2	55.00	110.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
500	176	1	2	25.00	50.00	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.orders (id, restaurant_id, order_number, status, subtotal, tax, total, currency, customer_id, customer_name, customer_phone, points_earned, points_redeemed, notes, completed_at, created_at, updated_at) FROM stdin;
1	1	ORD-001000	pending	395.00	19.75	414.75	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-04 11:05:00	2025-12-04 06:10:43
2	1	ORD-001001	completed	320.00	16.00	336.00	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-04 21:27:00	2025-12-04 06:10:43
3	1	ORD-001002	completed	20.00	1.00	21.00	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-04 15:34:00	2025-12-04 06:10:43
4	1	ORD-001003	pending	285.00	14.25	299.25	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-04 14:42:00	2025-12-04 06:10:43
5	1	ORD-001004	cancelled	120.00	6.00	126.00	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-04 11:48:00	2025-12-04 06:10:43
6	1	ORD-001005	completed	115.00	5.75	120.75	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-04 19:51:00	2025-12-04 06:10:43
7	1	ORD-001006	cancelled	380.00	19.00	399.00	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-05 21:10:00	2025-12-04 06:10:43
8	1	ORD-001007	completed	475.00	23.75	498.75	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-05 11:02:00	2025-12-04 06:10:43
9	1	ORD-001008	completed	350.00	17.50	367.50	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-05 21:28:00	2025-12-04 06:10:43
10	1	ORD-001009	pending	95.00	4.75	99.75	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-05 18:07:00	2025-12-04 06:10:43
11	1	ORD-001010	completed	220.00	11.00	231.00	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-06 12:49:00	2025-12-04 06:10:43
12	1	ORD-001011	pending	60.00	3.00	63.00	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-06 21:11:00	2025-12-04 06:10:43
13	1	ORD-001012	completed	245.00	12.25	257.25	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-06 19:22:00	2025-12-04 06:10:43
14	1	ORD-001013	completed	145.00	7.25	152.25	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-06 21:12:00	2025-12-04 06:10:43
15	1	ORD-001014	completed	230.00	11.50	241.50	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-06 18:01:00	2025-12-04 06:10:43
16	1	ORD-001015	cancelled	375.00	18.75	393.75	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-06 13:15:00	2025-12-04 06:10:43
17	1	ORD-001016	completed	255.00	12.75	267.75	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-06 16:50:00	2025-12-04 06:10:43
18	1	ORD-001017	completed	420.00	21.00	441.00	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-06 17:59:00	2025-12-04 06:10:43
19	1	ORD-001018	cancelled	60.00	3.00	63.00	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-07 18:26:00	2025-12-04 06:10:43
20	1	ORD-001019	pending	155.00	7.75	162.75	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-07 20:45:00	2025-12-04 06:10:43
21	1	ORD-001020	completed	235.00	11.75	246.75	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-07 14:55:00	2025-12-04 06:10:43
22	1	ORD-001021	completed	190.00	9.50	199.50	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-07 17:28:00	2025-12-04 06:10:43
23	1	ORD-001022	completed	75.00	3.75	78.75	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-08 16:19:00	2025-12-04 06:10:43
24	1	ORD-001023	pending	20.00	1.00	21.00	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-08 14:56:00	2025-12-04 06:10:43
25	1	ORD-001024	completed	95.00	4.75	99.75	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-08 18:57:00	2025-12-04 06:10:43
26	1	ORD-001025	completed	100.00	5.00	105.00	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-08 12:54:00	2025-12-04 06:10:43
27	1	ORD-001026	cancelled	30.00	1.50	31.50	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-08 22:19:00	2025-12-04 06:10:43
28	1	ORD-001027	pending	70.00	3.50	73.50	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-08 19:43:00	2025-12-04 06:10:43
29	1	ORD-001028	completed	230.00	11.50	241.50	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-09 14:29:00	2025-12-04 06:10:43
30	1	ORD-001029	completed	70.00	3.50	73.50	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-09 15:22:00	2025-12-04 06:10:43
31	1	ORD-001030	pending	195.00	9.75	204.75	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-09 13:47:00	2025-12-04 06:10:43
32	1	ORD-001031	completed	130.00	6.50	136.50	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-09 14:13:00	2025-12-04 06:10:43
33	1	ORD-001032	completed	60.00	3.00	63.00	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-09 11:24:00	2025-12-04 06:10:43
34	1	ORD-001033	pending	230.00	11.50	241.50	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-09 13:45:00	2025-12-04 06:10:43
35	1	ORD-001034	completed	75.00	3.75	78.75	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-10 19:53:00	2025-12-04 06:10:43
36	1	ORD-001035	pending	400.00	20.00	420.00	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-10 16:09:00	2025-12-04 06:10:43
37	1	ORD-001036	completed	360.00	18.00	378.00	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-10 18:13:00	2025-12-04 06:10:43
38	1	ORD-001037	completed	305.00	15.25	320.25	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-11 16:24:00	2025-12-04 06:10:43
39	1	ORD-001038	completed	120.00	6.00	126.00	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-11 16:51:00	2025-12-04 06:10:43
40	1	ORD-001039	completed	375.00	18.75	393.75	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-11 13:31:00	2025-12-04 06:10:43
41	1	ORD-001040	pending	220.00	11.00	231.00	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-11 12:30:00	2025-12-04 06:10:43
42	1	ORD-001041	completed	360.00	18.00	378.00	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-11 16:37:00	2025-12-04 06:10:43
43	1	ORD-001042	completed	240.00	12.00	252.00	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-11 22:16:00	2025-12-04 06:10:43
44	1	ORD-001043	completed	155.00	7.75	162.75	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-11 22:01:00	2025-12-04 06:10:43
45	1	ORD-001044	completed	140.00	7.00	147.00	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-11 18:24:00	2025-12-04 06:10:43
46	1	ORD-001045	completed	265.00	13.25	278.25	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-12 16:44:00	2025-12-04 06:10:43
47	1	ORD-001046	pending	120.00	6.00	126.00	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-12 19:40:00	2025-12-04 06:10:43
48	1	ORD-001047	completed	155.00	7.75	162.75	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-12 18:01:00	2025-12-04 06:10:43
49	1	ORD-001048	pending	270.00	13.50	283.50	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-12 17:33:00	2025-12-04 06:10:43
50	1	ORD-001049	completed	475.00	23.75	498.75	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-12 14:07:00	2025-12-04 06:10:43
51	1	ORD-001050	completed	340.00	17.00	357.00	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-12 22:41:00	2025-12-04 06:10:43
52	1	ORD-001051	completed	420.00	21.00	441.00	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-13 18:37:00	2025-12-04 06:10:43
53	1	ORD-001052	pending	180.00	9.00	189.00	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-13 17:03:00	2025-12-04 06:10:43
54	1	ORD-001053	completed	30.00	1.50	31.50	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-13 17:30:00	2025-12-04 06:10:43
55	1	ORD-001054	completed	260.00	13.00	273.00	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-13 17:58:00	2025-12-04 06:10:43
56	1	ORD-001055	pending	40.00	2.00	42.00	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-13 16:27:00	2025-12-04 06:10:43
57	1	ORD-001056	completed	180.00	9.00	189.00	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-13 14:31:00	2025-12-04 06:10:43
58	1	ORD-001057	cancelled	165.00	8.25	173.25	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-13 17:49:00	2025-12-04 06:10:43
59	1	ORD-001058	cancelled	20.00	1.00	21.00	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-14 14:57:00	2025-12-04 06:10:43
60	1	ORD-001059	completed	105.00	5.25	110.25	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-14 17:30:00	2025-12-04 06:10:43
61	1	ORD-001060	cancelled	90.00	4.50	94.50	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-14 14:37:00	2025-12-04 06:10:43
62	1	ORD-001061	completed	305.00	15.25	320.25	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-14 20:12:00	2025-12-04 06:10:43
63	1	ORD-001062	cancelled	70.00	3.50	73.50	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-14 16:09:00	2025-12-04 06:10:43
64	1	ORD-001063	pending	50.00	2.50	52.50	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-14 14:51:00	2025-12-04 06:10:43
65	1	ORD-001064	completed	290.00	14.50	304.50	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-14 12:28:00	2025-12-04 06:10:43
66	1	ORD-001065	completed	30.00	1.50	31.50	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-15 19:24:00	2025-12-04 06:10:43
67	1	ORD-001066	completed	200.00	10.00	210.00	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-15 18:04:00	2025-12-04 06:10:43
68	1	ORD-001067	pending	55.00	2.75	57.75	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-15 20:17:00	2025-12-04 06:10:43
69	1	ORD-001068	pending	135.00	6.75	141.75	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-15 18:51:00	2025-12-04 06:10:43
70	1	ORD-001069	completed	380.00	19.00	399.00	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-15 19:47:00	2025-12-04 06:10:43
71	1	ORD-001070	completed	325.00	16.25	341.25	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-15 15:04:00	2025-12-04 06:10:43
72	1	ORD-001071	completed	215.00	10.75	225.75	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-15 20:22:00	2025-12-04 06:10:43
73	1	ORD-001072	completed	255.00	12.75	267.75	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-16 20:43:00	2025-12-04 06:10:43
74	1	ORD-001073	completed	140.00	7.00	147.00	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-16 18:00:00	2025-12-04 06:10:43
75	1	ORD-001074	completed	55.00	2.75	57.75	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-16 15:27:00	2025-12-04 06:10:43
76	1	ORD-001075	pending	90.00	4.50	94.50	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-16 12:32:00	2025-12-04 06:10:43
77	1	ORD-001076	completed	105.00	5.25	110.25	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-16 12:31:00	2025-12-04 06:10:43
78	1	ORD-001077	completed	120.00	6.00	126.00	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-16 12:25:00	2025-12-04 06:10:43
79	1	ORD-001078	completed	255.00	12.75	267.75	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-16 17:52:00	2025-12-04 06:10:43
80	1	ORD-001079	completed	40.00	2.00	42.00	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-17 12:14:00	2025-12-04 06:10:43
81	1	ORD-001080	completed	655.00	32.75	687.75	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-17 13:12:00	2025-12-04 06:10:43
82	1	ORD-001081	completed	180.00	9.00	189.00	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-17 18:53:00	2025-12-04 06:10:43
83	1	ORD-001082	completed	140.00	7.00	147.00	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-18 13:32:00	2025-12-04 06:10:43
84	1	ORD-001083	pending	385.00	19.25	404.25	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-18 19:23:00	2025-12-04 06:10:43
85	1	ORD-001084	pending	305.00	15.25	320.25	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-18 18:39:00	2025-12-04 06:10:43
86	1	ORD-001085	cancelled	195.00	9.75	204.75	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-18 13:45:00	2025-12-04 06:10:43
87	1	ORD-001086	completed	295.00	14.75	309.75	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-19 14:25:00	2025-12-04 06:10:43
88	1	ORD-001087	completed	360.00	18.00	378.00	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-19 21:28:00	2025-12-04 06:10:43
89	1	ORD-001088	pending	20.00	1.00	21.00	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-19 17:31:00	2025-12-04 06:10:43
90	1	ORD-001089	pending	340.00	17.00	357.00	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-19 19:50:00	2025-12-04 06:10:43
91	1	ORD-001090	cancelled	25.00	1.25	26.25	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-19 20:00:00	2025-12-04 06:10:43
92	1	ORD-001091	completed	225.00	11.25	236.25	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-19 11:41:00	2025-12-04 06:10:43
93	1	ORD-001092	cancelled	340.00	17.00	357.00	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-20 16:34:00	2025-12-04 06:10:43
94	1	ORD-001093	completed	410.00	20.50	430.50	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-20 16:38:00	2025-12-04 06:10:43
95	1	ORD-001094	completed	430.00	21.50	451.50	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-20 12:18:00	2025-12-04 06:10:43
96	1	ORD-001095	pending	190.00	9.50	199.50	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-20 15:26:00	2025-12-04 06:10:43
97	1	ORD-001096	completed	120.00	6.00	126.00	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-21 14:57:00	2025-12-04 06:10:43
98	1	ORD-001097	completed	190.00	9.50	199.50	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-21 18:32:00	2025-12-04 06:10:43
99	1	ORD-001098	completed	360.00	18.00	378.00	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-21 19:45:00	2025-12-04 06:10:43
100	1	ORD-001099	completed	265.00	13.25	278.25	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-21 11:55:00	2025-12-04 06:10:43
101	1	ORD-001100	cancelled	50.00	2.50	52.50	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-21 14:24:00	2025-12-04 06:10:43
102	1	ORD-001101	completed	155.00	7.75	162.75	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-21 19:28:00	2025-12-04 06:10:43
103	1	ORD-001102	completed	185.00	9.25	194.25	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-21 16:00:00	2025-12-04 06:10:43
104	1	ORD-001103	completed	290.00	14.50	304.50	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-22 15:04:00	2025-12-04 06:10:43
105	1	ORD-001104	completed	135.00	6.75	141.75	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-22 12:39:00	2025-12-04 06:10:43
106	1	ORD-001105	pending	150.00	7.50	157.50	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-22 17:41:00	2025-12-04 06:10:43
107	1	ORD-001106	completed	295.00	14.75	309.75	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-22 12:34:00	2025-12-04 06:10:43
108	1	ORD-001107	completed	110.00	5.50	115.50	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-22 17:39:00	2025-12-04 06:10:43
109	1	ORD-001108	cancelled	160.00	8.00	168.00	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-22 12:48:00	2025-12-04 06:10:43
110	1	ORD-001109	cancelled	225.00	11.25	236.25	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-23 20:10:00	2025-12-04 06:10:43
111	1	ORD-001110	pending	50.00	2.50	52.50	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-23 21:40:00	2025-12-04 06:10:43
112	1	ORD-001111	cancelled	395.00	19.75	414.75	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-23 21:47:00	2025-12-04 06:10:43
113	1	ORD-001112	completed	210.00	10.50	220.50	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-23 18:10:00	2025-12-04 06:10:43
114	1	ORD-001113	completed	230.00	11.50	241.50	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-23 18:02:00	2025-12-04 06:10:43
115	1	ORD-001114	completed	165.00	8.25	173.25	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-24 15:25:00	2025-12-04 06:10:43
116	1	ORD-001115	completed	80.00	4.00	84.00	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-24 13:36:00	2025-12-04 06:10:43
117	1	ORD-001116	completed	65.00	3.25	68.25	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-24 17:11:00	2025-12-04 06:10:43
118	1	ORD-001117	completed	105.00	5.25	110.25	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-24 20:55:00	2025-12-04 06:10:43
119	1	ORD-001118	cancelled	135.00	6.75	141.75	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-24 11:50:00	2025-12-04 06:10:43
120	1	ORD-001119	completed	165.00	8.25	173.25	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-25 18:31:00	2025-12-04 06:10:43
121	1	ORD-001120	cancelled	55.00	2.75	57.75	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-25 11:29:00	2025-12-04 06:10:43
122	1	ORD-001121	completed	105.00	5.25	110.25	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-25 22:35:00	2025-12-04 06:10:43
123	1	ORD-001122	cancelled	270.00	13.50	283.50	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-25 18:00:00	2025-12-04 06:10:43
124	1	ORD-001123	pending	160.00	8.00	168.00	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-25 12:17:00	2025-12-04 06:10:43
125	1	ORD-001124	completed	400.00	20.00	420.00	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-25 17:56:00	2025-12-04 06:10:43
126	1	ORD-001125	cancelled	420.00	21.00	441.00	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-26 21:13:00	2025-12-04 06:10:43
127	1	ORD-001126	completed	280.00	14.00	294.00	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-26 15:39:00	2025-12-04 06:10:43
128	1	ORD-001127	completed	240.00	12.00	252.00	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-26 11:51:00	2025-12-04 06:10:43
129	1	ORD-001128	completed	105.00	5.25	110.25	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-26 19:07:00	2025-12-04 06:10:43
130	1	ORD-001129	completed	395.00	19.75	414.75	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-26 20:33:00	2025-12-04 06:10:43
131	1	ORD-001130	pending	310.00	15.50	325.50	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-26 18:11:00	2025-12-04 06:10:43
132	1	ORD-001131	pending	75.00	3.75	78.75	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-26 20:52:00	2025-12-04 06:10:43
133	1	ORD-001132	cancelled	315.00	15.75	330.75	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-27 13:40:00	2025-12-04 06:10:43
134	1	ORD-001133	pending	85.00	4.25	89.25	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-27 22:05:00	2025-12-04 06:10:43
135	1	ORD-001134	cancelled	380.00	19.00	399.00	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-27 21:43:00	2025-12-04 06:10:43
136	1	ORD-001135	pending	60.00	3.00	63.00	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-27 19:09:00	2025-12-04 06:10:43
137	1	ORD-001136	pending	10.00	0.50	10.50	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-27 17:38:00	2025-12-04 06:10:43
138	1	ORD-001137	completed	295.00	14.75	309.75	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-11-28 12:55:00	2025-12-04 06:10:43
139	1	ORD-001138	pending	275.00	13.75	288.75	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-11-28 15:52:00	2025-12-04 06:10:43
140	1	ORD-001139	completed	480.00	24.00	504.00	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-28 20:18:00	2025-12-04 06:10:43
141	1	ORD-001140	completed	190.00	9.50	199.50	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-29 18:42:00	2025-12-04 06:10:43
142	1	ORD-001141	pending	115.00	5.75	120.75	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-29 17:19:00	2025-12-04 06:10:43
143	1	ORD-001142	completed	475.00	23.75	498.75	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-29 19:18:00	2025-12-04 06:10:43
144	1	ORD-001143	pending	155.00	7.75	162.75	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-29 12:46:00	2025-12-04 06:10:43
145	1	ORD-001144	completed	285.00	14.25	299.25	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-11-29 19:38:00	2025-12-04 06:10:43
146	1	ORD-001145	completed	125.00	6.25	131.25	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-11-29 15:49:00	2025-12-04 06:10:43
147	1	ORD-001146	cancelled	400.00	20.00	420.00	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-29 14:37:00	2025-12-04 06:10:43
148	1	ORD-001147	completed	30.00	1.50	31.50	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-30 19:58:00	2025-12-04 06:10:43
149	1	ORD-001148	completed	65.00	3.25	68.25	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-30 11:46:00	2025-12-04 06:10:43
150	1	ORD-001149	completed	90.00	4.50	94.50	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-11-30 18:30:00	2025-12-04 06:10:43
151	1	ORD-001150	completed	160.00	8.00	168.00	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-11-30 14:36:00	2025-12-04 06:10:43
152	1	ORD-001151	completed	270.00	13.50	283.50	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-11-30 22:17:00	2025-12-04 06:10:43
153	1	ORD-001152	completed	75.00	3.75	78.75	AED	5	Omar Abdullah	+971-50-0000005	0	0	\N	\N	2025-11-30 22:23:00	2025-12-04 06:10:43
154	1	ORD-001153	completed	50.00	2.50	52.50	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-11-30 15:39:00	2025-12-04 06:10:43
155	1	ORD-001154	pending	205.00	10.25	215.25	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-11-30 12:51:00	2025-12-04 06:10:43
156	1	ORD-001155	completed	255.00	12.75	267.75	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-12-01 15:12:00	2025-12-04 06:10:43
157	1	ORD-001156	pending	130.00	6.50	136.50	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-12-01 16:38:00	2025-12-04 06:10:43
158	1	ORD-001157	completed	75.00	3.75	78.75	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-12-01 15:12:00	2025-12-04 06:10:43
159	1	ORD-001158	pending	175.00	8.75	183.75	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-12-01 19:59:00	2025-12-04 06:10:43
160	1	ORD-001159	pending	275.00	13.75	288.75	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-12-01 22:17:00	2025-12-04 06:10:43
161	1	ORD-001160	completed	85.00	4.25	89.25	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-12-02 11:02:00	2025-12-04 06:10:43
162	1	ORD-001161	completed	400.00	20.00	420.00	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-12-02 20:29:00	2025-12-04 06:10:43
163	1	ORD-001162	completed	70.00	3.50	73.50	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-12-02 17:43:00	2025-12-04 06:10:43
164	1	ORD-001163	completed	520.00	26.00	546.00	AED	4	Sara Ahmed	+971-50-0000004	0	0	\N	\N	2025-12-03 12:33:00	2025-12-04 06:10:43
165	1	ORD-001164	completed	155.00	7.75	162.75	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-12-03 21:34:00	2025-12-04 06:10:43
166	1	ORD-001165	cancelled	30.00	1.50	31.50	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-12-03 13:43:00	2025-12-04 06:10:43
167	1	ORD-001166	completed	405.00	20.25	425.25	AED	6	Layla Ibrahim	+971-50-0000006	0	0	\N	\N	2025-12-03 15:51:00	2025-12-04 06:10:43
168	1	ORD-001167	cancelled	50.00	2.50	52.50	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-12-03 16:17:00	2025-12-04 06:10:43
169	1	ORD-001168	completed	100.00	5.00	105.00	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-12-03 22:27:00	2025-12-04 06:10:43
170	1	ORD-001169	cancelled	320.00	16.00	336.00	AED	8	Noor Saleh	+971-50-0000008	0	0	\N	\N	2025-12-03 11:21:00	2025-12-04 06:10:43
171	1	ORD-001170	completed	195.00	9.75	204.75	AED	9	Khaled Rashid	+971-50-0000009	0	0	\N	\N	2025-12-04 21:41:00	2025-12-04 06:10:43
172	1	ORD-001171	completed	290.00	14.50	304.50	AED	7	Youssef Mahmoud	+971-50-0000007	0	0	\N	\N	2025-12-04 17:56:00	2025-12-04 06:10:43
173	1	ORD-001172	pending	85.00	4.25	89.25	AED	2	Fatima Hassan	+971-50-0000002	0	0	\N	\N	2025-12-04 17:30:00	2025-12-04 06:10:43
174	1	ORD-001173	cancelled	145.00	7.25	152.25	AED	3	Mohammed Khalid	+971-50-0000003	0	0	\N	\N	2025-12-04 21:52:00	2025-12-04 06:10:43
175	1	ORD-001174	completed	285.00	14.25	299.25	AED	10	Maryam Yousef	+971-50-0000010	0	0	\N	\N	2025-12-04 12:54:00	2025-12-04 06:10:43
176	1	ORD-001175	cancelled	285.00	14.25	299.25	AED	1	Ahmed Ali	+971-50-0000001	0	0	\N	\N	2025-12-04 17:18:00	2025-12-04 06:10:43
\.


--
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.payments (id, subscription_id, stripe_payment_intent_id, amount, currency, status, payment_method, metadata, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: plans; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.plans (id, name, slug, price_monthly, price_yearly, currency, features, is_active, created_at, updated_at) FROM stdin;
1	Basic	basic	99.00	990.00	AED	["1 restaurant","Up to 5 staff members","Basic menu management","Order tracking","Email support"]	t	2025-12-04 06:06:41	2025-12-04 06:06:41
2	Pro	pro	299.00	2990.00	AED	["3 restaurants","Unlimited staff members","Advanced menu management","Real-time order tracking","Analytics & reports","Priority support"]	t	2025-12-04 06:06:41	2025-12-04 06:06:41
3	Enterprise	enterprise	799.00	7990.00	AED	["Unlimited restaurants","Unlimited staff members","Advanced menu management","Real-time order tracking","Advanced analytics & reports","API access","Custom integrations","Dedicated support"]	t	2025-12-04 06:06:41	2025-12-04 06:06:41
\.


--
-- Data for Name: point_transactions; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.point_transactions (id, customer_id, order_id, reward_redemption_id, type, points, description, balance_after, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: restaurants; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.restaurants (id, name, slug, description, phone, email, address, city, country, currency, locale, settings, created_at, updated_at) FROM stdin;
1	Demo Restaurant	demo-restaurant	\N	\N	\N	\N	\N	\N	AED	en	\N	2025-12-04 06:10:43	2025-12-04 06:10:43
\.


--
-- Data for Name: reward_redemptions; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.reward_redemptions (id, customer_id, reward_id, order_id, points_used, status, code, used_at, expires_at, metadata, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: rewards; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.rewards (id, restaurant_id, name, description, points_required, reward_type, discount_value, menu_item_id, max_redemptions, redemptions_count, valid_from, valid_until, is_active, sort_order, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.


--
-- Data for Name: staff; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.staff (id, user_id, restaurant_id, role, is_active, invited_at, joined_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: subscriptions; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.subscriptions (id, stripe_subscription_id, stripe_customer_id, status, plan_slug, billing_cycle, starts_at, ends_at, canceled_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: tenants; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.tenants (id, identifier, plan_id, subscription_status, subscription_ends_at, data, created_at, updated_at) FROM stdin;
demo	demo	2	active	2026-12-04 06:08:03	[]	2025-12-04 06:08:03	2025-12-04 06:08:03
b79581da-ce6b-40c4-9fc0-80a77532f8fc	testrestaurant	2	active	\N	[]	2025-12-04 06:17:03	2025-12-04 06:17:03
72bd94a7-1fcb-4802-8fe3-f252d2b08a6c	ahmadtest	1	active	\N	{"created_at":"2025-12-06 06:21:42","updated_at":"2025-12-06 06:21:42"}	2025-12-06 06:21:42	2025-12-06 06:23:19
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
1	Demo Admin	admin@demo.com	\N	$2y$12$alXowh/r.lj/tBlmxEybY.zkNkATPq0URroU7T0YUwvx9aSnEEyxi	\N	2025-12-04 06:10:22	2025-12-04 06:10:22
\.


--
-- Name: customers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.customers_id_seq', 10, true);


--
-- Name: domains_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.domains_id_seq', 4, true);


--
-- Name: earning_methods_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.earning_methods_id_seq', 1, true);


--
-- Name: loyalty_points_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.loyalty_points_id_seq', 1, false);


--
-- Name: menu_categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.menu_categories_id_seq', 4, true);


--
-- Name: menu_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.menu_items_id_seq', 14, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.migrations_id_seq', 20, true);


--
-- Name: order_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.order_items_id_seq', 500, true);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.orders_id_seq', 176, true);


--
-- Name: payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.payments_id_seq', 1, false);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.permissions_id_seq', 1, false);


--
-- Name: plans_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.plans_id_seq', 4, true);


--
-- Name: point_transactions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.point_transactions_id_seq', 1, false);


--
-- Name: restaurants_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.restaurants_id_seq', 1, true);


--
-- Name: reward_redemptions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.reward_redemptions_id_seq', 1, false);


--
-- Name: rewards_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.rewards_id_seq', 1, false);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.roles_id_seq', 8, true);


--
-- Name: staff_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.staff_id_seq', 1, false);


--
-- Name: subscriptions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.subscriptions_id_seq', 1, false);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.users_id_seq', 1, true);


--
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (id);


--
-- Name: customers customers_restaurant_id_phone_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_restaurant_id_phone_unique UNIQUE (restaurant_id, phone);


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
-- Name: earning_methods earning_methods_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.earning_methods
    ADD CONSTRAINT earning_methods_pkey PRIMARY KEY (id);


--
-- Name: loyalty_points loyalty_points_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.loyalty_points
    ADD CONSTRAINT loyalty_points_pkey PRIMARY KEY (id);


--
-- Name: menu_categories menu_categories_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.menu_categories
    ADD CONSTRAINT menu_categories_pkey PRIMARY KEY (id);


--
-- Name: menu_items menu_items_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.menu_items
    ADD CONSTRAINT menu_items_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: order_items order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_pkey PRIMARY KEY (id);


--
-- Name: orders orders_order_number_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_order_number_unique UNIQUE (order_number);


--
-- Name: orders orders_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_pkey PRIMARY KEY (id);


--
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- Name: payments payments_stripe_payment_intent_id_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_stripe_payment_intent_id_unique UNIQUE (stripe_payment_intent_id);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


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
-- Name: point_transactions point_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.point_transactions
    ADD CONSTRAINT point_transactions_pkey PRIMARY KEY (id);


--
-- Name: restaurants restaurants_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.restaurants
    ADD CONSTRAINT restaurants_pkey PRIMARY KEY (id);


--
-- Name: restaurants restaurants_slug_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.restaurants
    ADD CONSTRAINT restaurants_slug_unique UNIQUE (slug);


--
-- Name: reward_redemptions reward_redemptions_code_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.reward_redemptions
    ADD CONSTRAINT reward_redemptions_code_unique UNIQUE (code);


--
-- Name: reward_redemptions reward_redemptions_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.reward_redemptions
    ADD CONSTRAINT reward_redemptions_pkey PRIMARY KEY (id);


--
-- Name: rewards rewards_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.rewards
    ADD CONSTRAINT rewards_pkey PRIMARY KEY (id);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: staff staff_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_pkey PRIMARY KEY (id);


--
-- Name: subscriptions subscriptions_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_pkey PRIMARY KEY (id);


--
-- Name: subscriptions subscriptions_stripe_subscription_id_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.subscriptions
    ADD CONSTRAINT subscriptions_stripe_subscription_id_unique UNIQUE (stripe_subscription_id);


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
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: customers_phone_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX customers_phone_index ON public.customers USING btree (phone);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: point_transactions_customer_id_type_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX point_transactions_customer_id_type_index ON public.point_transactions USING btree (customer_id, type);


--
-- Name: point_transactions_expires_at_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX point_transactions_expires_at_index ON public.point_transactions USING btree (expires_at);


--
-- Name: reward_redemptions_code_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX reward_redemptions_code_index ON public.reward_redemptions USING btree (code);


--
-- Name: reward_redemptions_customer_id_status_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX reward_redemptions_customer_id_status_index ON public.reward_redemptions USING btree (customer_id, status);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: ahmadmhala
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: customers customers_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


--
-- Name: domains domains_tenant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.domains
    ADD CONSTRAINT domains_tenant_id_foreign FOREIGN KEY (tenant_id) REFERENCES public.tenants(id) ON DELETE CASCADE;


--
-- Name: earning_methods earning_methods_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.earning_methods
    ADD CONSTRAINT earning_methods_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


--
-- Name: loyalty_points loyalty_points_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.loyalty_points
    ADD CONSTRAINT loyalty_points_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE CASCADE;


--
-- Name: menu_categories menu_categories_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.menu_categories
    ADD CONSTRAINT menu_categories_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


--
-- Name: menu_items menu_items_menu_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.menu_items
    ADD CONSTRAINT menu_items_menu_category_id_foreign FOREIGN KEY (menu_category_id) REFERENCES public.menu_categories(id) ON DELETE CASCADE;


--
-- Name: menu_items menu_items_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.menu_items
    ADD CONSTRAINT menu_items_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: order_items order_items_menu_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_menu_item_id_foreign FOREIGN KEY (menu_item_id) REFERENCES public.menu_items(id) ON DELETE CASCADE;


--
-- Name: order_items order_items_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: orders orders_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE SET NULL;


--
-- Name: orders orders_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


--
-- Name: payments payments_subscription_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_subscription_id_foreign FOREIGN KEY (subscription_id) REFERENCES public.subscriptions(id) ON DELETE SET NULL;


--
-- Name: point_transactions point_transactions_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.point_transactions
    ADD CONSTRAINT point_transactions_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE CASCADE;


--
-- Name: point_transactions point_transactions_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.point_transactions
    ADD CONSTRAINT point_transactions_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE SET NULL;


--
-- Name: point_transactions point_transactions_reward_redemption_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.point_transactions
    ADD CONSTRAINT point_transactions_reward_redemption_id_foreign FOREIGN KEY (reward_redemption_id) REFERENCES public.reward_redemptions(id) ON DELETE SET NULL;


--
-- Name: reward_redemptions reward_redemptions_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.reward_redemptions
    ADD CONSTRAINT reward_redemptions_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE CASCADE;


--
-- Name: reward_redemptions reward_redemptions_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.reward_redemptions
    ADD CONSTRAINT reward_redemptions_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE SET NULL;


--
-- Name: reward_redemptions reward_redemptions_reward_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.reward_redemptions
    ADD CONSTRAINT reward_redemptions_reward_id_foreign FOREIGN KEY (reward_id) REFERENCES public.rewards(id) ON DELETE CASCADE;


--
-- Name: rewards rewards_menu_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.rewards
    ADD CONSTRAINT rewards_menu_item_id_foreign FOREIGN KEY (menu_item_id) REFERENCES public.menu_items(id) ON DELETE SET NULL;


--
-- Name: rewards rewards_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.rewards
    ADD CONSTRAINT rewards_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: staff staff_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


--
-- Name: staff staff_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.staff
    ADD CONSTRAINT staff_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: tenants tenants_plan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.tenants
    ADD CONSTRAINT tenants_plan_id_foreign FOREIGN KEY (plan_id) REFERENCES public.plans(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict 8mAG9KqiEybU6uDYrpz9IF6D6UTh9L5xTdaYG93RnET7shA2PKYx3SHe4vOL6C5

