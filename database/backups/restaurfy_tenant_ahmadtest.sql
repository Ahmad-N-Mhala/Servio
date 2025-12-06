--
-- PostgreSQL database dump
--

\restrict MJOtHoSl94Yzh9GTWqkoT2rzWr22J0GZkubaXRNDVLWClwXL7zA1GJtXxVMptm7

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
    updated_at timestamp(0) without time zone,
    min_spent numeric(10,2),
    max_points integer,
    currency_amount numeric(10,2) DEFAULT '1'::numeric NOT NULL
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
    updated_at timestamp(0) without time zone,
    type character varying(255) DEFAULT 'dine_in'::character varying NOT NULL,
    table_id bigint,
    payment_status character varying(191) DEFAULT 'unpaid'::character varying NOT NULL,
    payment_method character varying(191),
    CONSTRAINT orders_type_check CHECK (((type)::text = ANY ((ARRAY['dine_in'::character varying, 'takeaway'::character varying])::text[])))
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
-- Name: restaurant_tables; Type: TABLE; Schema: public; Owner: ahmadmhala
--

CREATE TABLE public.restaurant_tables (
    id bigint NOT NULL,
    restaurant_id bigint NOT NULL,
    name character varying(191) NOT NULL,
    capacity integer DEFAULT 4 NOT NULL,
    status character varying(255) DEFAULT 'available'::character varying NOT NULL,
    location character varying(191),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT restaurant_tables_status_check CHECK (((status)::text = ANY ((ARRAY['available'::character varying, 'occupied'::character varying, 'reserved'::character varying])::text[])))
);


ALTER TABLE public.restaurant_tables OWNER TO ahmadmhala;

--
-- Name: restaurant_tables_id_seq; Type: SEQUENCE; Schema: public; Owner: ahmadmhala
--

CREATE SEQUENCE public.restaurant_tables_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.restaurant_tables_id_seq OWNER TO ahmadmhala;

--
-- Name: restaurant_tables_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: ahmadmhala
--

ALTER SEQUENCE public.restaurant_tables_id_seq OWNED BY public.restaurant_tables.id;


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
-- Name: point_transactions id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.point_transactions ALTER COLUMN id SET DEFAULT nextval('public.point_transactions_id_seq'::regclass);


--
-- Name: restaurant_tables id; Type: DEFAULT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.restaurant_tables ALTER COLUMN id SET DEFAULT nextval('public.restaurant_tables_id_seq'::regclass);


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
1	1	5550100	Jane Doe	jane@example.com	\N	\N	0	1500.00	silver	\N	t	2025-12-06 12:29:00	2025-12-06 12:29:00
2	1	5550101	John Smith	john@example.com	\N	\N	0	5500.00	platinum	\N	t	2025-12-06 12:29:00	2025-12-06 12:29:00
\.


--
-- Data for Name: earning_methods; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.earning_methods (id, restaurant_id, name, description, type, points, is_active, created_at, updated_at, min_spent, max_points, currency_amount) FROM stdin;
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
1	1	{"en":"Appetizers","ar":"Appetizers"}	Delicious Appetizers	0	t	2025-12-06 12:29:00	2025-12-06 12:29:00
2	1	{"en":"Main Course","ar":"Main Course"}	Delicious Main Course	1	t	2025-12-06 12:29:00	2025-12-06 12:29:00
3	1	{"en":"Beverages","ar":"Beverages"}	Delicious Beverages	2	t	2025-12-06 12:29:00	2025-12-06 12:29:00
4	1	{"en":"Desserts","ar":"Desserts"}	Delicious Desserts	3	t	2025-12-06 12:29:00	2025-12-06 12:29:00
\.


--
-- Data for Name: menu_items; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.menu_items (id, restaurant_id, menu_category_id, name, description, price, currency, image, is_available, sort_order, allergens, created_at, updated_at) FROM stdin;
1	1	1	{"en":"Hummus","ar":"Hummus"}	Creamy chickpea dip with olive oil	15.00	AED	\N	t	0	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
2	1	1	{"en":"Fattoush","ar":"Fattoush"}	Mixed green salad with toasted bread	18.00	AED	\N	t	0	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
3	1	2	{"en":"Grilled Chicken","ar":"Grilled Chicken"}	Half chicken marinated and grilled	45.00	AED	\N	t	0	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
4	1	2	{"en":"Lamb Kabsa","ar":"Lamb Kabsa"}	Traditional rice dish with lamb	55.00	AED	\N	t	0	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
5	1	2	{"en":"Beef Burger","ar":"Beef Burger"}	Angus beef patty with cheese and fries	40.00	AED	\N	t	0	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
6	1	3	{"en":"Mint Lemonade","ar":"Mint Lemonade"}	Freshly squeezed lemon with mint	12.00	AED	\N	t	0	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
7	1	3	{"en":"Turkish Coffee","ar":"Turkish Coffee"}	Traditional strong coffee	10.00	AED	\N	t	0	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
8	1	4	{"en":"Kunafa","ar":"Kunafa"}	Sweet cheese pastry	25.00	AED	\N	t	0	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2024_01_01_100000_create_restaurants_table	1
2	2024_01_01_100001_create_users_table	1
3	2024_01_01_100002_create_customers_table	1
4	2024_01_01_100003_create_staff_table	1
5	2024_01_01_100004_create_menu_categories_table	1
6	2024_01_01_100005_create_menu_items_table	1
7	2024_01_01_100006_create_orders_table	1
8	2024_01_01_100007_create_order_items_table	1
9	2024_01_01_100008_create_subscriptions_table	1
10	2024_01_01_100009_create_payments_table	1
11	2024_01_01_100010_create_permission_tables	1
12	2024_01_01_100012_create_loyalty_points_table	1
13	2024_01_01_100013_create_rewards_table	1
14	2024_01_01_100014_create_reward_redemptions_table	1
15	2024_01_01_100015_create_point_transactions_table	1
16	2025_12_06_075449_create_earning_methods_table	1
17	2025_12_06_081121_add_conditions_to_earning_methods_table	1
18	2025_12_06_082447_add_currency_amount_to_earning_methods_table	1
19	2025_12_06_113916_create_restaurant_tables_table	1
20	2025_12_06_113922_add_type_and_table_id_to_orders_table	1
21	2025_12_06_164500_add_payment_status_to_orders_table	2
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
1	1	3	1	45.00	45.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
2	1	6	1	12.00	12.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
3	1	7	2	10.00	20.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
4	2	1	2	15.00	30.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
5	2	4	1	55.00	55.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
6	2	8	2	25.00	50.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
7	3	6	1	12.00	12.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
8	3	7	1	10.00	10.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
9	3	8	2	25.00	50.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
10	4	1	2	15.00	30.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
11	4	4	2	55.00	110.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
12	4	8	2	25.00	50.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
13	5	3	1	45.00	45.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
14	5	6	1	12.00	12.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
15	5	8	2	25.00	50.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
16	6	8	2	25.00	50.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
17	7	3	1	45.00	45.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
18	7	4	1	55.00	55.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
19	7	8	1	25.00	25.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
20	8	1	1	15.00	15.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
21	8	4	2	55.00	110.00	\N	2025-12-06 12:29:00	2025-12-06 12:29:00
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.orders (id, restaurant_id, order_number, status, subtotal, tax, total, currency, customer_id, customer_name, customer_phone, points_earned, points_redeemed, notes, completed_at, created_at, updated_at, type, table_id, payment_status, payment_method) FROM stdin;
1	1	ORD-53961	completed	77.00	3.85	80.85	AED	1	Jane Doe	5550100	0	0	\N	2025-11-29 12:29:00	2025-12-06 12:29:00	2025-12-06 12:29:00	dine_in	1	unpaid	\N
2	1	ORD-10676	completed	135.00	6.75	141.75	AED	1	Jane Doe	5550100	0	0	\N	2025-12-01 12:29:00	2025-12-06 12:29:00	2025-12-06 12:29:00	dine_in	1	unpaid	\N
3	1	ORD-25579	completed	72.00	3.60	75.60	AED	1	Jane Doe	5550100	0	0	\N	2025-12-04 12:29:00	2025-12-06 12:29:00	2025-12-06 12:29:00	dine_in	1	unpaid	\N
4	1	ORD-36486	completed	190.00	9.50	199.50	AED	1	Jane Doe	5550100	0	0	\N	2025-12-06 12:29:00	2025-12-06 12:29:00	2025-12-06 12:29:00	dine_in	1	unpaid	\N
5	1	ORD-16237	completed	107.00	5.35	112.35	AED	1	Jane Doe	5550100	0	0	\N	2025-12-02 12:29:00	2025-12-06 12:29:00	2025-12-06 12:29:00	dine_in	1	unpaid	\N
6	1	ORD-KIT-6603	pending	50.00	2.50	52.50	AED	1	Jane Doe	5550100	0	0	\N	\N	2025-12-06 12:29:00	2025-12-06 12:29:00	dine_in	1	unpaid	\N
7	1	ORD-KIT-8605	processing	125.00	6.25	131.25	AED	1	Jane Doe	5550100	0	0	\N	\N	2025-12-06 12:29:00	2025-12-06 12:29:00	dine_in	1	unpaid	\N
8	1	ORD-KIT-1635	processing	125.00	6.25	131.25	AED	1	Jane Doe	5550100	0	0	\N	\N	2025-12-06 12:29:00	2025-12-06 12:29:00	dine_in	1	unpaid	\N
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
-- Data for Name: point_transactions; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.point_transactions (id, customer_id, order_id, reward_redemption_id, type, points, description, balance_after, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: restaurant_tables; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.restaurant_tables (id, restaurant_id, name, capacity, status, location, created_at, updated_at) FROM stdin;
1	1	T-1	4	available	\N	2025-12-06 12:13:16	2025-12-06 12:13:16
2	1	T-2	4	available	Main Hall	2025-12-06 12:29:00	2025-12-06 12:29:00
3	1	T-3	4	reserved	Main Hall	2025-12-06 12:29:00	2025-12-06 12:29:00
4	1	T-4	6	available	Family Section	2025-12-06 12:29:00	2025-12-06 12:29:00
5	1	T-5	2	occupied	Window	2025-12-06 12:29:00	2025-12-06 12:29:00
\.


--
-- Data for Name: restaurants; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.restaurants (id, name, slug, description, phone, email, address, city, country, currency, locale, settings, created_at, updated_at) FROM stdin;
1	Ahmad Restaurant	ahmad-restaurant	\N	1234567890	contact@ahmadtest.com	\N	\N	\N	AED	en	\N	2025-12-06 12:11:42	2025-12-06 12:11:42
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
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: ahmadmhala
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
1	Ahmad Admin	admin@ahmadtest.com	\N	$2y$12$2wmQYAjF23pWGeZ7tI0Aj.nLwoVXlsCsk9P/wScgvVh0NL9p8Ps1u	\N	2025-12-06 12:08:31	2025-12-06 12:08:31
\.


--
-- Name: customers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.customers_id_seq', 2, true);


--
-- Name: earning_methods_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.earning_methods_id_seq', 1, false);


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

SELECT pg_catalog.setval('public.menu_items_id_seq', 8, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.migrations_id_seq', 21, true);


--
-- Name: order_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.order_items_id_seq', 21, true);


--
-- Name: orders_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.orders_id_seq', 8, true);


--
-- Name: payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.payments_id_seq', 1, false);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.permissions_id_seq', 1, false);


--
-- Name: point_transactions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.point_transactions_id_seq', 1, false);


--
-- Name: restaurant_tables_id_seq; Type: SEQUENCE SET; Schema: public; Owner: ahmadmhala
--

SELECT pg_catalog.setval('public.restaurant_tables_id_seq', 5, true);


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

SELECT pg_catalog.setval('public.roles_id_seq', 1, false);


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
-- Name: point_transactions point_transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.point_transactions
    ADD CONSTRAINT point_transactions_pkey PRIMARY KEY (id);


--
-- Name: restaurant_tables restaurant_tables_pkey; Type: CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.restaurant_tables
    ADD CONSTRAINT restaurant_tables_pkey PRIMARY KEY (id);


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
-- Name: customers customers_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


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
-- Name: orders orders_table_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_table_id_foreign FOREIGN KEY (table_id) REFERENCES public.restaurant_tables(id) ON DELETE SET NULL;


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
-- Name: restaurant_tables restaurant_tables_restaurant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: ahmadmhala
--

ALTER TABLE ONLY public.restaurant_tables
    ADD CONSTRAINT restaurant_tables_restaurant_id_foreign FOREIGN KEY (restaurant_id) REFERENCES public.restaurants(id) ON DELETE CASCADE;


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
-- PostgreSQL database dump complete
--

\unrestrict MJOtHoSl94Yzh9GTWqkoT2rzWr22J0GZkubaXRNDVLWClwXL7zA1GJtXxVMptm7

