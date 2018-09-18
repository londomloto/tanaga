--
-- Worksaurus PostgreSQL Backup
--
-- Hostname : localhost:5432
-- Database : wsteam
--
-- Date: 2018-02-15 07:29:11
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';

SET search_path = public, pg_catalog;

SET default_tablespace = '';

SET default_with_oids = false;

CREATE TABLE bpm_diagrams (
    id bigserial NOT NULL,
    type character varying(50) DEFAULT 'activity'::character varying,
    name character varying(100),
    slug character varying(255),
    description character varying(255),
    cover character varying(255),
    created_date timestamp with time zone,
    created_by character varying(255)
);


CREATE TABLE bpm_forms (
    bf_id bigserial NOT NULL,
    bf_activity bigint,
    bf_name character varying(100),
    bf_description character varying(200),
    bf_tpl_file character varying(100),
    bf_tpl_orig character varying(100)
);


CREATE TABLE bpm_forms_roles (
    bfr_id bigserial NOT NULL,
    bfr_bf_id bigint,
    bfr_sr_id bigint
);


CREATE TABLE bpm_forms_users (
    bfu_id bigserial NOT NULL,
    bfu_bf_id bigint,
    bfu_su_id bigint
);


CREATE TABLE bpm_links (
    id bigserial NOT NULL,
    name character varying(100),
    client_id character varying(50),
    client_source character varying(50),
    client_target character varying(50),
    type character varying(100),
    router_type character varying(255),
    diagram_id bigint,
    source_id bigint,
    target_id bigint,
    command character varying(1000),
    stroke character varying(50),
    label character varying(200),
    label_distance double precision DEFAULT (0.5)::double precision,
    convex integer DEFAULT 1,
    smooth integer DEFAULT 1,
    smoothness bigint,
    data_source text,
    params text
);


CREATE TABLE bpm_shapes (
    id bigserial NOT NULL,
    client_id character varying(50),
    client_parent character varying(50),
    client_pool character varying(50),
    type character varying(100),
    mode character varying(50),
    diagram_id bigint,
    parent_id bigint,
    width double precision,
    height double precision,
    "left" double precision,
    top double precision,
    rotate double precision,
    label character varying(100),
    fill character varying(30),
    stroke character varying(30),
    stroke_width bigint,
    data_source text,
    params character varying(1000)
);


CREATE TABLE dx_auth (
    auth_col_id bigserial NOT NULL,
    group_child_id bigint,
    map_id bigint,
    dx_read bigint,
    dx_write bigint,
    dx_edit bigint,
    dx_delete bigint,
    dx_default bigint
);


CREATE TABLE dx_mapping (
    map_id bigserial NOT NULL,
    map_profile_id bigint NOT NULL,
    map_table character varying(50) NOT NULL,
    map_type character varying(20) DEFAULT 'data'::character varying,
    map_xls_col character varying(50) NOT NULL,
    map_tbl_col character varying(50),
    map_dtype character varying(50) DEFAULT 'string'::character varying,
    map_pk bigint DEFAULT (0)::bigint,
    map_mandatory bigint DEFAULT (0)::bigint,
    map_ref_table character varying(50),
    map_ref_col character varying(50),
    map_ref_fk character varying(50),
    map_ref_ignore bigint DEFAULT (0)::bigint,
    map_active bigint,
    map_col_alias character varying(100),
    map_grp_seq bigint,
    map_sk bigint DEFAULT (0)::bigint,
    map_ref_contents text,
    map_col_seq bigint
);


CREATE TABLE dx_profiles (
    profile_id bigserial NOT NULL,
    profile_name character varying(50),
    profile_desc character varying(200),
    header_row_idx bigint,
    col_offset character varying(20),
    row_offset bigint,
    map_header bigint,
    has_merge_cell integer
);


CREATE TABLE kanban (
    panel_id bigserial NOT NULL,
    panel_color character varying(100),
    panel_card_filter character varying(100),
    panel_title character varying(100)
);

CREATE TABLE kanban_forms (
    kf_id bigserial NOT NULL,
    kf_diagrams_id bigint,
    kf_status bigint,
    kf_form_edit character varying(255),
    kf_form_view character varying(255)
);



CREATE TABLE kanban_panels (
    kp_id bigserial NOT NULL,
    kp_ks_id bigint,
    kp_title character varying(50),
    kp_accent character varying(30),
    kp_order bigint DEFAULT (1)::bigint
);


CREATE TABLE kanban_settings (
    ks_id bigserial NOT NULL,
    ks_name character varying(100),
    ks_description character varying(200),
    ks_api character varying(100),
    ks_image character varying(255)
);


CREATE TABLE kanban_statuses (
    kst_id bigserial NOT NULL,
    kst_kp_id bigint,
    kst_diagrams_id bigint,
    kst_status bigint,
    kst_color character varying(20)
);


CREATE TABLE sys_captcha (
    id character varying(40) NOT NULL,
    namespace character varying(32) NOT NULL,
    code character varying(32) NOT NULL,
    code_display character varying(32) NOT NULL,
    created bigint NOT NULL,
    audio_data bytea
);

CREATE TABLE sys_config (
    sc_id bigserial NOT NULL,
    sc_name character varying(255),
    sc_value character varying(255)
);


CREATE TABLE sys_labels (
    sl_label character varying(255),
    sl_created_by integer,
    sl_created_dt timestamp without time zone,
    sl_color character varying(50) DEFAULT 'var(--paper-blue-grey-500)'::character varying,
    sl_id bigserial NOT NULL,
    sl_sp_id bigint
);


CREATE TABLE sys_menus (
    smn_id bigserial NOT NULL,
    smn_parent_id bigint DEFAULT (0)::bigint,
    smn_title character varying(50),
    smn_icon character varying(30),
    smn_path character varying(100),
    smn_order bigint DEFAULT (1)::bigint,
    smn_visible integer DEFAULT 1,
    smn_default integer DEFAULT 0
);


CREATE TABLE sys_modules (
    sm_id bigserial NOT NULL,
    sm_name character varying(100),
    sm_version character varying(30),
    sm_title character varying(100),
    sm_author character varying(50) DEFAULT 'KCT Team'::character varying,
    sm_repository character varying(255),
    sm_api character varying(255)
);

CREATE TABLE sys_modules_capabilities (
    smc_id bigserial NOT NULL,
    smc_sm_id bigint,
    smc_name character varying(100),
    smc_description character varying(255)
);



CREATE TABLE sys_numbers (
    sn_id bigserial NOT NULL,
    sn_name character varying(50),
    sn_value bigint,
    sn_length bigint,
    sn_prefix character varying(30),
    sn_suffix character varying(30)
);


CREATE TABLE sys_permissions (
    sp_id bigserial NOT NULL,
    sp_sr_id bigint,
    sp_smc_id bigint
);


CREATE TABLE sys_projects (
    sp_name character varying(255),
    sp_title character varying(255),
    sp_desc text,
    sp_creator_id bigint,
    sp_created_date timestamp without time zone,
    sp_worksheet_id bigint,
    sp_id bigserial NOT NULL
);

CREATE TABLE sys_projects_labels (
    spl_id bigserial NOT NULL,
    spl_sp_id bigint,
    spl_sl_id bigint
);



CREATE TABLE sys_projects_users (
    spu_sp_id bigint,
    spu_su_id bigint,
    spu_id bigserial NOT NULL
);


CREATE TABLE sys_roles (
    sr_id bigserial NOT NULL,
    sr_name character varying(100),
    sr_slug character varying(100),
    sr_description character varying(255),
    sr_default integer DEFAULT 0,
    sr_created_date timestamp with time zone DEFAULT now(),
    sr_created_by character varying(50) DEFAULT 'system'::character varying
);

CREATE TABLE sys_roles_kanban (
    srk_id bigserial NOT NULL,
    srk_sr_id bigint,
    srk_ks_id bigint,
    srk_selected integer DEFAULT 0
);


CREATE TABLE sys_roles_menus (
    srm_id bigserial NOT NULL,
    srm_sr_id bigint,
    srm_smn_id bigint,
    srm_selected integer DEFAULT 0
);


CREATE TABLE sys_roles_panels (
    srs_id bigserial NOT NULL,
    srs_sp_id bigint,
    srs_sr_id bigint,
    srs_kp_id bigint,
    srs_kst_id bigint,
    srs_checked smallint DEFAULT 0
);


CREATE TABLE sys_roles_permissions (
    srp_id bigserial NOT NULL,
    srp_sr_id bigint,
    srp_smc_id bigint,
    srp_selected integer DEFAULT 0
);



CREATE TABLE sys_users (
    su_id bigserial NOT NULL,
    su_sr_id bigint,
    su_email character varying(255),
    su_passwd character varying(255),
    su_fullname character varying(255),
    su_avatar character varying(255),
    su_access_token text,
    su_refresh_token text,
    su_sex character varying(30),
    su_dob date,
    su_job_title character varying(100),
    su_company character varying(255),
    su_active integer DEFAULT 1,
    su_created_date timestamp with time zone DEFAULT now(),
    su_created_by character varying(50) DEFAULT 'system'::character varying,
    su_invite_token text,
    su_recover_token text
);

CREATE TABLE sys_users_kanban (
    suk_id bigserial NOT NULL,
    suk_su_id bigint,
    suk_ks_id bigint,
    suk_selected integer DEFAULT 0
);


CREATE TABLE sys_users_menus (
    sum_id bigserial NOT NULL,
    sum_su_id bigint,
    sum_smn_id bigint,
    sum_selected integer DEFAULT 0
);


CREATE TABLE sys_users_panels (
    sus_id bigserial NOT NULL,
    sus_sp_id bigint,
    sus_su_id bigint,
    sus_kp_id bigint,
    sus_kst_id bigint,
    sus_checked smallint DEFAULT 0
);


CREATE TABLE sys_users_permissions (
    sup_id bigserial NOT NULL,
    sup_su_id bigint,
    sup_smc_id bigint,
    sup_selected integer DEFAULT 0
);



CREATE TABLE trx_tasks (
    tt_id bigserial NOT NULL,
    tt_title character varying(255),
    tt_flag character varying(255),
    tt_sp_id bigint,
    tt_desc text,
    tt_due_date date,
    tt_creator_id bigint,
    tt_created_dt timestamp without time zone,
    tt_slug character varying(255)
);

CREATE TABLE trx_tasks_activities (
    tta_id bigserial NOT NULL,
    tta_tt_id bigint,
    tta_type character varying(50),
    tta_sender bigint,
    tta_created timestamp without time zone,
    tta_data text,
    tta_file character varying(255)
);


CREATE TABLE trx_tasks_comments (
    ttc_tt_id bigint,
    ttc_sender bigint,
    ttc_message text,
    ttc_id bigserial NOT NULL
);


CREATE TABLE trx_tasks_labels (
    ttl_id bigserial NOT NULL,
    ttl_tt_id bigint,
    ttl_sl_id bigint
);


CREATE TABLE trx_tasks_statuses (
    tts_id bigserial NOT NULL,
    tts_tt_id bigint,
    tts_status bigint,
    tts_target bigint,
    tts_worker character varying(100),
    tts_deleted integer,
    tts_created timestamp without time zone,
    tts_slug character varying(255)
);



CREATE TABLE trx_tasks_users (
    ttu_tt_id bigint,
    ttu_su_id bigint,
    ttu_id bigserial NOT NULL
);


ALTER TABLE ONLY bpm_diagrams ALTER COLUMN id SET DEFAULT nextval('bpm_diagrams_id_seq'::regclass);

ALTER TABLE ONLY bpm_forms ALTER COLUMN bf_id SET DEFAULT nextval('bpm_forms_bf_id_seq'::regclass);

ALTER TABLE ONLY bpm_forms_roles ALTER COLUMN bfr_id SET DEFAULT nextval('bpm_forms_roles_bfr_id_seq'::regclass);

ALTER TABLE ONLY bpm_forms_users ALTER COLUMN bfu_id SET DEFAULT nextval('bpm_forms_users_bfu_id_seq'::regclass);

ALTER TABLE ONLY bpm_links ALTER COLUMN id SET DEFAULT nextval('bpm_links_id_seq'::regclass);

ALTER TABLE ONLY bpm_shapes ALTER COLUMN id SET DEFAULT nextval('bpm_shapes_id_seq'::regclass);

ALTER TABLE ONLY dx_auth ALTER COLUMN auth_col_id SET DEFAULT nextval('dx_auth_auth_col_id_seq'::regclass);

ALTER TABLE ONLY dx_mapping ALTER COLUMN map_id SET DEFAULT nextval('dx_mapping_map_id_seq'::regclass);

ALTER TABLE ONLY dx_profiles ALTER COLUMN profile_id SET DEFAULT nextval('dx_profiles_profile_id_seq'::regclass);

ALTER TABLE ONLY kanban ALTER COLUMN panel_id SET DEFAULT nextval('kanban_panel_id_seq'::regclass);

ALTER TABLE ONLY kanban_forms ALTER COLUMN kf_id SET DEFAULT nextval('kanban_forms_kf_id_seq'::regclass);

ALTER TABLE ONLY kanban_panels ALTER COLUMN kp_id SET DEFAULT nextval('kanban_panels_kp_id_seq'::regclass);

ALTER TABLE ONLY kanban_settings ALTER COLUMN ks_id SET DEFAULT nextval('kanban_settings_ks_id_seq'::regclass);

ALTER TABLE ONLY kanban_statuses ALTER COLUMN kst_id SET DEFAULT nextval('kanban_statuses_kst_id_seq'::regclass);

ALTER TABLE ONLY sys_config ALTER COLUMN sc_id SET DEFAULT nextval('sys_config_sc_id_seq'::regclass);

ALTER TABLE ONLY sys_labels ALTER COLUMN sl_id SET DEFAULT nextval('sys_labels_sl_id_seq'::regclass);

ALTER TABLE ONLY sys_menus ALTER COLUMN smn_id SET DEFAULT nextval('sys_menus_smn_id_seq'::regclass);

ALTER TABLE ONLY sys_modules ALTER COLUMN sm_id SET DEFAULT nextval('sys_modules_sm_id_seq'::regclass);

ALTER TABLE ONLY sys_modules_capabilities ALTER COLUMN smc_id SET DEFAULT nextval('sys_modules_capabilities_smc_id_seq'::regclass);

ALTER TABLE ONLY sys_numbers ALTER COLUMN sn_id SET DEFAULT nextval('sys_numbers_sn_id_seq'::regclass);

ALTER TABLE ONLY sys_permissions ALTER COLUMN sp_id SET DEFAULT nextval('sys_permissions_sp_id_seq'::regclass);

ALTER TABLE ONLY sys_projects ALTER COLUMN sp_id SET DEFAULT nextval('sys_projects_sp_id_seq'::regclass);

ALTER TABLE ONLY sys_projects_labels ALTER COLUMN spl_id SET DEFAULT nextval('sys_projects_labels_spl_id_seq'::regclass);

ALTER TABLE ONLY sys_projects_users ALTER COLUMN spu_id SET DEFAULT nextval('sys_projects_users_spu_id_seq'::regclass);

ALTER TABLE ONLY sys_roles ALTER COLUMN sr_id SET DEFAULT nextval('sys_roles_sr_id_seq'::regclass);

ALTER TABLE ONLY sys_roles_kanban ALTER COLUMN srk_id SET DEFAULT nextval('sys_roles_kanban_srk_id_seq'::regclass);

ALTER TABLE ONLY sys_roles_menus ALTER COLUMN srm_id SET DEFAULT nextval('sys_roles_menus_srm_id_seq'::regclass);

ALTER TABLE ONLY sys_roles_panels ALTER COLUMN srs_id SET DEFAULT nextval('sys_roles_panels_srs_id_seq'::regclass);

ALTER TABLE ONLY sys_roles_permissions ALTER COLUMN srp_id SET DEFAULT nextval('sys_roles_permissions_srp_id_seq'::regclass);

ALTER TABLE ONLY sys_users ALTER COLUMN su_id SET DEFAULT nextval('sys_users_su_id_seq'::regclass);

ALTER TABLE ONLY sys_users_kanban ALTER COLUMN suk_id SET DEFAULT nextval('sys_users_kanban_suk_id_seq'::regclass);

ALTER TABLE ONLY sys_users_menus ALTER COLUMN sum_id SET DEFAULT nextval('sys_users_menus_sum_id_seq'::regclass);

ALTER TABLE ONLY sys_users_panels ALTER COLUMN sus_id SET DEFAULT nextval('sys_users_panels_sus_id_seq'::regclass);

ALTER TABLE ONLY sys_users_permissions ALTER COLUMN sup_id SET DEFAULT nextval('sys_users_permissions_sup_id_seq'::regclass);

ALTER TABLE ONLY trx_tasks ALTER COLUMN tt_id SET DEFAULT nextval('trx_tasks_tt_id_seq'::regclass);

ALTER TABLE ONLY trx_tasks_activities ALTER COLUMN tta_id SET DEFAULT nextval('trx_tasks_activities_tta_id_seq'::regclass);

ALTER TABLE ONLY trx_tasks_comments ALTER COLUMN ttc_id SET DEFAULT nextval('trx_tasks_comments_ttc_id_seq'::regclass);

ALTER TABLE ONLY trx_tasks_labels ALTER COLUMN ttl_id SET DEFAULT nextval('trx_tasks_labels_ttl_id_seq'::regclass);

ALTER TABLE ONLY trx_tasks_statuses ALTER COLUMN tts_id SET DEFAULT nextval('trx_tasks_statuses_tts_id_seq'::regclass);

ALTER TABLE ONLY trx_tasks_users ALTER COLUMN ttu_id SET DEFAULT nextval('trx_tasks_users_ttu_id_seq'::regclass);

INSERT INTO bpm_diagrams VALUES (59, 'Graph.diagram.type.Activity', 'Backlog Workflow', 'backlog-workflow', 'Task flow follow backlog activity', 'a287f0fbdaa88ff613f29bbd54f4b376.jpg', '2017-12-21 18:31:50+00', NULL);
INSERT INTO bpm_diagrams VALUES (57, 'Graph.diagram.type.Activity', 'Classic Workflow', 'classic-workflow', 'No diagram description', '61cc035e820bfde59742818bd5d4f53d.jpg', '2017-12-05 09:39:42+00', NULL);
INSERT INTO bpm_diagrams VALUES (58, 'Graph.diagram.type.Activity', 'Simple Workflow', 'simple-workflow', 'Only todo and doing task flow', '527f315653751b03885334a9a09a0c7e.jpg', '2017-12-21 18:28:18+00', NULL);

SELECT pg_catalog.setval('bpm_diagrams_id_seq', 59, true);

INSERT INTO bpm_forms VALUES (12, 808, 'Site planning upload template', 'No description', '19d1f246a90d48307faeac3fdb31a30e.html', 'upload.html');
INSERT INTO bpm_forms VALUES (13, 809, 'Approval template', 'No description', '6f64be85304054dee25e47629da7c6d3.html', 'approval.html');
INSERT INTO bpm_forms VALUES (14, 813, 'Create SSR template', 'No description', 'fe6683b844815e3e1864772e20fab81c.html', 'create-site-ssr.html');
INSERT INTO bpm_forms VALUES (15, 821, 'Sent Data SSR template', 'No description', '73a72a7da1f948d2ae66541209894d24.html', 'send-data-ssr.html');
INSERT INTO bpm_forms VALUES (16, 819, 'Add far end template', 'No description', '6edbcdb933fae65132a71f658fb14038.html', 'add-far-end.html');
INSERT INTO bpm_forms VALUES (17, 817, 'Review far end template', 'No description', 'dbce59b50bb42ef7997246eea071a6f7.html', 'review-far-end.html');
INSERT INTO bpm_forms VALUES (18, 811, 'Update prodef template', 'No description', '6fb5ce6462d47296620683c9d779ce27.html', 'upload-prodef.html');
INSERT INTO bpm_forms VALUES (19, 825, 'Open Opportunity Form', 'No description', 'e870a901848a6d2859d15f04d6284630.html', 'oportunity_form_2.html');
INSERT INTO bpm_forms VALUES (30, 863, 'Form Done', 'No description', '4b9faff43018b7fbb9b1645917035430.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (29, 862, 'Form Doing', 'No description', 'f93d3cce7efb21ee2667d46f0249fb2d.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (28, 861, 'Form Todo', 'No description', 'b518cb7a6dc5bb53dcc7a10aee7c2e79.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (27, 860, 'Form Start', 'No description', '0217ac43eeb0d07bde0a5db69540dab0.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (31, 869, 'Task Editor', 'No description', '11342055cc8a7c59f879c3192f35f614.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (35, 873, 'Task Editor', 'No description', 'a5d965a5d94d1368b0254e6f8d489e4a.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (34, 872, 'Task Editor', 'No description', '1661b1e82c1f7daeb026c195099ca4c9.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (33, 870, 'Task Editor', 'No description', '6b0d21713aa2bef4a4598df191880d1c.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (32, 871, 'Task Editor', 'No description', 'dfc6bab2068a87382e5e5bca931f6a2f.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (36, 865, 'Task Editor', 'No description', 'ab4a86c6bc894025905802523d622852.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (37, 866, 'Task Editor', 'No description', 'd0e8547eef285d1df62ca261c7a60ebf.html', 'task-editor.html');
INSERT INTO bpm_forms VALUES (38, 867, 'Task Editor', 'No description', '3f2da945af15c580c09724d5da3d2f31.html', 'task-editor.html');

SELECT pg_catalog.setval('bpm_forms_bf_id_seq', 38, true);

INSERT INTO bpm_forms_roles VALUES (9, 12, 1);
INSERT INTO bpm_forms_roles VALUES (10, 13, 1);
INSERT INTO bpm_forms_roles VALUES (16, 31, 4);
INSERT INTO bpm_forms_roles VALUES (17, 35, 4);
INSERT INTO bpm_forms_roles VALUES (18, 34, 4);
INSERT INTO bpm_forms_roles VALUES (19, 33, 4);
INSERT INTO bpm_forms_roles VALUES (20, 32, 4);

SELECT pg_catalog.setval('bpm_forms_roles_bfr_id_seq', 23, true);

INSERT INTO bpm_forms_users VALUES (6, 12, 1);
INSERT INTO bpm_forms_users VALUES (7, 12, 7);
INSERT INTO bpm_forms_users VALUES (8, 13, 1);
INSERT INTO bpm_forms_users VALUES (9, 13, 7);
INSERT INTO bpm_forms_users VALUES (10, 14, 1);
INSERT INTO bpm_forms_users VALUES (11, 14, 7);
INSERT INTO bpm_forms_users VALUES (12, 15, 1);
INSERT INTO bpm_forms_users VALUES (13, 15, 7);
INSERT INTO bpm_forms_users VALUES (14, 16, 1);
INSERT INTO bpm_forms_users VALUES (15, 16, 7);
INSERT INTO bpm_forms_users VALUES (16, 17, 1);
INSERT INTO bpm_forms_users VALUES (17, 17, 7);
INSERT INTO bpm_forms_users VALUES (18, 18, 1);
INSERT INTO bpm_forms_users VALUES (19, 18, 7);
INSERT INTO bpm_forms_users VALUES (20, 19, 7);

SELECT pg_catalog.setval('bpm_forms_users_bfu_id_seq', 26, true);

INSERT INTO bpm_links VALUES (670, 'done', 'graph-link-3', 'graph-shape-3', 'graph-shape-4', 'Graph.link.Orthogonal', 'orthogonal', 57, 862, 863, 'M602.4480809840677,342.50883277161284L602.4480809840678,434.50883277161284', '#000', 'Done', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Done","operator":""}]');
INSERT INTO bpm_links VALUES (671, NULL, 'graph-link-4', 'graph-shape-4', 'graph-shape-5', 'Graph.link.Orthogonal', 'orthogonal', 57, 863, 864, 'M559.4480809840621,494.508832771613L559.4480809840621,564.508832771611', '#000', '', 0.5, 1, 0, 6, NULL, '[]');
INSERT INTO bpm_links VALUES (668, 'todo', 'graph-link-1', 'graph-shape-1', 'graph-shape-2', 'Graph.link.Orthogonal', 'orthogonal', 57, 860, 861, 'M559.4480809840627,84.5088327716124L559.4480809840636,138.50883277161284', '#000', 'Todo', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Todo","operator":""}]');
INSERT INTO bpm_links VALUES (669, 'doing', 'graph-link-2', 'graph-shape-2', 'graph-shape-3', 'Graph.link.Orthogonal', 'orthogonal', 57, 861, 862, 'M603.4480809840641,198.5088327716124L603.4480809840646,282.5088327716114', '#000', 'Doing', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Doing","operator":""}]');
INSERT INTO bpm_links VALUES (675, 'active', 'graph-link-8', 'graph-shape-10', 'graph-shape-12', 'Graph.link.Orthogonal', 'orthogonal', 59, 869, 871, 'M513.0000000000001,151.21875000000077L513.0000000000001,216.21875000000045', '#000', 'Active', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Active","operator":""}]');
INSERT INTO bpm_links VALUES (676, 'backlog', 'graph-link-9', 'graph-shape-12', 'graph-shape-11', 'Graph.link.Orthogonal', 'orthogonal', 59, 871, 870, 'M443,246.21875L278.0000000000005,246.21875L277.9999999999998,337.21875000000045', '#000', 'Backlog', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Backlog","operator":""}]');
INSERT INTO bpm_links VALUES (677, NULL, 'graph-link-10', 'graph-shape-11', 'graph-shape-15', 'Graph.link.Orthogonal', 'orthogonal', 59, 870, 874, 'M278.0000000000003,397.21874999999966L278,519.21875L483.40429701180904,519.2187500000001', '#000', '', 0.5, 1, 0, 6, NULL, '[]');
INSERT INTO bpm_links VALUES (678, 'resolved', 'graph-link-11', 'graph-shape-12', 'graph-shape-13', 'Graph.link.Orthogonal', 'orthogonal', 59, 871, 872, 'M583.0000000000001,246.21874999999991L739,246.21875L739.0000000000005,337.2187500000009', '#000', 'Resolved', 0.516197307309469999, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Resolved","operator":""}]');
INSERT INTO bpm_links VALUES (679, 'others', 'graph-link-12', 'graph-shape-12', 'graph-shape-14', 'Graph.link.Orthogonal', 'orthogonal', 59, 871, 873, 'M512.999999999998,276.2187500000005L512.999999999998,337.2187499999995', '#000', 'Others', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Others","operator":""}]');
INSERT INTO bpm_links VALUES (684, 'todo', 'graph-link-7', 'graph-shape-4', 'graph-shape-2', 'Graph.link.Orthogonal', 'orthogonal', 57, 863, 861, 'M629.4480809840635,464.5088327716137L716.4480809840662,464.5088327716128L716.4480809840662,168.5088327716128L629.4480809840635,168.5088327716149', '#000', 'Todo', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Todo","operator":""}]');
INSERT INTO bpm_links VALUES (685, 'done', 'graph-link-8', 'graph-shape-2', 'graph-shape-4', 'Graph.link.Orthogonal', 'orthogonal', 57, 861, 863, 'M489.4480809840635,168.50883277161256L415.44808098405724,168.5088327716128L415.44808098405724,464.5088327716128L489.4480809840635,464.5088327716123', '#000', 'Done', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Done","operator":""}]');
INSERT INTO bpm_links VALUES (682, 'doing', 'graph-link-5', 'graph-shape-4', 'graph-shape-3', 'Graph.link.Orthogonal', 'orthogonal', 57, 863, 862, 'M517.4480809840614,434.50883277161233L517.4480809840591,342.5088327716124', '#000', 'Doing', 0.502720288949400018, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Doing","operator":""}]');
INSERT INTO bpm_links VALUES (672, 'todo', 'graph-link-1', 'graph-shape-1', 'graph-shape-2', 'Graph.link.Orthogonal', 'orthogonal', 58, 865, 866, 'M558.0000000000008,129.2187499999997L558.0000000000011,190.21874999999966', '#000', 'Todo', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Todo","operator":""}]');
INSERT INTO bpm_links VALUES (673, 'doing', 'graph-link-2', 'graph-shape-2', 'graph-shape-3', 'Graph.link.Orthogonal', 'orthogonal', 58, 866, 867, 'M488,220.21874999999963L406.9999999999999,220.21875L406.9999999999999,335.21875L488,335.2187500000003', '#000', 'Doing', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Doing","operator":""}]');
INSERT INTO bpm_links VALUES (686, 'todo', 'graph-link-4', 'graph-shape-3', 'graph-shape-2', 'Graph.link.Orthogonal', 'orthogonal', 58, 867, 866, 'M628,335.21875000000097L733.9999999999984,335.21875L733.9999999999984,220.21875L628,220.21874999999963', '#000', 'Todo', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Todo","operator":""}]');
INSERT INTO bpm_links VALUES (674, NULL, 'graph-link-3', 'graph-shape-3', 'graph-shape-4', 'Graph.link.Orthogonal', 'orthogonal', 58, 867, 868, 'M558,365.2187500000003L558.0000000000016,421.2187499999992', '#000', '', 0.5, 1, 0, 6, NULL, '[]');
INSERT INTO bpm_links VALUES (680, NULL, 'graph-link-13', 'graph-shape-13', 'graph-shape-15', 'Graph.link.Orthogonal', 'orthogonal', 59, 872, 874, 'M739.000000000004,397.21874999999886L739,519.21875L542.5957029881874,519.2187500000016', '#000', '', 0.5, 1, 0, 6, NULL, '[]');
INSERT INTO bpm_links VALUES (681, NULL, 'graph-link-14', 'graph-shape-14', 'graph-shape-15', 'Graph.link.Orthogonal', 'orthogonal', 59, 873, 874, 'M513.0000000000022,397.21874999999943L512.9999999999982,489.2187500000006', '#000', '', 0.5, 1, 0, 6, NULL, '[]');
INSERT INTO bpm_links VALUES (683, 'todo', 'graph-link-6', 'graph-shape-3', 'graph-shape-2', 'Graph.link.Orthogonal', 'orthogonal', 57, 862, 861, 'M517.4480809840638,282.5088327716132L517.448080984063,198.50883277161284', '#000', 'Todo', 0.5, 1, 0, 6, NULL, '[{"field":"tt_flag","comparison":"=","value":"Todo","operator":""}]');

SELECT pg_catalog.setval('bpm_links_id_seq', 686, true);

INSERT INTO bpm_shapes VALUES (869, 'graph-shape-10', NULL, NULL, 'Graph.shape.activity.Start', NULL, 59, NULL, 60, 60, 483, 91.21875, 0, 'Start', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (871, 'graph-shape-12', NULL, NULL, 'Graph.shape.activity.Action', NULL, 59, NULL, 140, 60, 443, 216.21875, 0, 'Active Form', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (872, 'graph-shape-13', NULL, NULL, 'Graph.shape.activity.Action', NULL, 59, NULL, 140, 60, 669, 337.21875, 0, 'Resolved Form', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (870, 'graph-shape-11', NULL, NULL, 'Graph.shape.activity.Action', NULL, 59, NULL, 140, 60, 208, 337.21875, 0, 'Backlog Form', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (873, 'graph-shape-14', NULL, NULL, 'Graph.shape.activity.Action', NULL, 59, NULL, 140, 60, 443, 337.21875, 0, 'Others Form', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (874, 'graph-shape-15', NULL, NULL, 'Graph.shape.activity.Final', NULL, 59, NULL, 60, 60, 483, 489.21875, 0, 'End', '#FF4081', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (860, 'graph-shape-1', NULL, NULL, 'Graph.shape.activity.Start', NULL, 57, NULL, 60, 60, 529.44808098405997, 24.508832771613001, 0, 'Start', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (861, 'graph-shape-2', NULL, NULL, 'Graph.shape.activity.Action', NULL, 57, NULL, 140, 60, 489.448080984060027, 138.508832771610003, 0, 'Todo Form', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (862, 'graph-shape-3', NULL, NULL, 'Graph.shape.activity.Action', NULL, 57, NULL, 140, 60, 489.448080984060027, 282.508832771610003, 0, 'Doing Form', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (863, 'graph-shape-4', NULL, NULL, 'Graph.shape.activity.Action', NULL, 57, NULL, 140, 60, 489.448080984060027, 434.508832771610003, 0, 'Done Form', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (864, 'graph-shape-5', NULL, NULL, 'Graph.shape.activity.Final', NULL, 57, NULL, 60, 60, 529.44808098405997, 564.508832771609946, 0, 'End', '#FF4081', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (865, 'graph-shape-1', NULL, NULL, 'Graph.shape.activity.Start', NULL, 58, NULL, 60, 60, 528, 69.21875, 0, 'Start', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (866, 'graph-shape-2', NULL, NULL, 'Graph.shape.activity.Action', NULL, 58, NULL, 140, 60, 488, 190.21875, 0, 'Todo Form', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (867, 'graph-shape-3', NULL, NULL, 'Graph.shape.activity.Action', NULL, 58, NULL, 140, 60, 488, 305.21875, 0, 'Doing Form', 'rgb(255, 255, 255)', 'rgb(0, 0, 0)', 2, NULL, '[]');
INSERT INTO bpm_shapes VALUES (868, 'graph-shape-4', NULL, NULL, 'Graph.shape.activity.Final', NULL, 58, NULL, 60, 60, 528, 421.21875, 0, 'End', '#FF4081', 'rgb(0, 0, 0)', 2, NULL, '[]');

SELECT pg_catalog.setval('bpm_shapes_id_seq', 874, true);

SELECT pg_catalog.setval('dx_auth_auth_col_id_seq', 1, true);

INSERT INTO dx_mapping VALUES (1, 1, 'example_1', 'data', 'EMAIL', 'email', 'string', 1, 0, NULL, NULL, NULL, 0, 1, NULL, 1, 0, NULL, NULL);
INSERT INTO dx_mapping VALUES (3, 1, 'example_1', 'data', 'DEBUG', 'debug', 'string', 0, 0, NULL, NULL, NULL, 0, 1, NULL, 1, 0, NULL, NULL);
INSERT INTO dx_mapping VALUES (4, 1, 'example_1', 'data', 'SEX', 'sex', 'int', 0, 0, NULL, NULL, NULL, 0, 1, NULL, 1, 0, NULL, NULL);
INSERT INTO dx_mapping VALUES (5, 1, 'example_2', 'data', 'USERNAME', 'username', 'string', 1, 0, NULL, NULL, NULL, 0, 0, NULL, 1, 0, NULL, NULL);
INSERT INTO dx_mapping VALUES (6, 1, 'example_1', 'data', 'DOB', 'dob', 'date', 0, 0, NULL, NULL, NULL, 0, 1, NULL, 1, 0, NULL, NULL);
INSERT INTO dx_mapping VALUES (7, 1, 'example_1', 'data', 'AVATAR', 'avatar', 'string', 0, 0, NULL, NULL, NULL, 0, 1, NULL, 1, 0, NULL, NULL);
INSERT INTO dx_mapping VALUES (8, 1, 'example_1', 'data', 'POINTS', 'points', 'double', 0, 0, NULL, NULL, NULL, 0, 1, NULL, 1, 0, NULL, NULL);
INSERT INTO dx_mapping VALUES (9, 1, 'example_1', 'data', 'FULLNAME', 'fullname', 'string', 0, 0, NULL, NULL, NULL, 0, 1, NULL, 1, 0, NULL, NULL);
INSERT INTO dx_mapping VALUES (10, 4, 'A', 'data', '1', 'A', 'A', 1, 0, NULL, NULL, NULL, 0, 1, NULL, NULL, 1, NULL, NULL);

SELECT pg_catalog.setval('dx_mapping_map_id_seq', 10, true);

INSERT INTO dx_profiles VALUES (1, 'Example Profile', 'Example profile', 1, 'B', 2, 1, NULL);
INSERT INTO dx_profiles VALUES (4, 'foo', NULL, 1, 'A', 2, 1, NULL);
INSERT INTO dx_profiles VALUES (5, 'bar', NULL, 1, 'A', 1, 1, NULL);

SELECT pg_catalog.setval('dx_profiles_profile_id_seq', 5, true);

INSERT INTO kanban VALUES (1, 'pink', 'isCardFilterTodo', 'To Do');
INSERT INTO kanban VALUES (2, 'blue', 'isCardFilterDoing', 'Doing');
INSERT INTO kanban VALUES (3, 'green', 'isCardFilterDone', 'Done');

INSERT INTO kanban_forms VALUES (1, 49, 396, '', '');
INSERT INTO kanban_forms VALUES (2, 49, 397, '', '');
INSERT INTO kanban_forms VALUES (3, 49, 397, 'Capture.JPG', '');
INSERT INTO kanban_forms VALUES (4, 28, 96, '', '');
INSERT INTO kanban_forms VALUES (5, 28, 97, '', '');
INSERT INTO kanban_forms VALUES (6, 28, 98, '', '');
INSERT INTO kanban_forms VALUES (7, 28, 99, '', '');
INSERT INTO kanban_forms VALUES (8, 28, 105, '', '');

SELECT pg_catalog.setval('kanban_forms_kf_id_seq', 8, true);

SELECT pg_catalog.setval('kanban_panel_id_seq', 3, true);

INSERT INTO kanban_panels VALUES (66, 20, 'Backlog', '#9c27b0', 0);
INSERT INTO kanban_panels VALUES (67, 20, 'Active', '#2196f3', 1);
INSERT INTO kanban_panels VALUES (68, 20, 'Resolved', '#ffc107', 2);
INSERT INTO kanban_panels VALUES (71, 20, 'Others', '#e91e63', 3);
INSERT INTO kanban_panels VALUES (76, 19, 'Todo', '#9c27b0', 0);
INSERT INTO kanban_panels VALUES (77, 19, 'Doing', '#2196f3', 1);
INSERT INTO kanban_panels VALUES (78, 19, 'Done', '#ffc107', 2);
INSERT INTO kanban_panels VALUES (69, 21, 'Todo', '#8F7EE6', 0);
INSERT INTO kanban_panels VALUES (70, 21, 'Doing', '#00AAFF', 1);

SELECT pg_catalog.setval('kanban_panels_kp_id_seq', 86, true);

INSERT INTO kanban_settings VALUES (20, 'Backlog', 'Backlog, active and resolved workflow', '/m2m', 'backlog.png');
INSERT INTO kanban_settings VALUES (19, 'Classic', 'Todo, doing and done workflow', '/kanban', 'classic.png');
INSERT INTO kanban_settings VALUES (21, 'Simple', 'Only todo and doing', '/non-gsm', 'simple.png');

SELECT pg_catalog.setval('kanban_settings_ks_id_seq', 21, true);

INSERT INTO kanban_statuses VALUES (76, 66, 59, 676, '#000');
INSERT INTO kanban_statuses VALUES (77, 67, 59, 675, '#000');
INSERT INTO kanban_statuses VALUES (78, 68, 59, 678, '#000');
INSERT INTO kanban_statuses VALUES (79, 71, 59, 679, '#000');
INSERT INTO kanban_statuses VALUES (81, 76, 57, 668, '#000');
INSERT INTO kanban_statuses VALUES (85, 76, 57, 683, '#000');
INSERT INTO kanban_statuses VALUES (86, 76, 57, 684, '#000');
INSERT INTO kanban_statuses VALUES (82, 77, 57, 669, '#000');
INSERT INTO kanban_statuses VALUES (83, 77, 57, 682, '#000');
INSERT INTO kanban_statuses VALUES (84, 78, 57, 670, '#000');
INSERT INTO kanban_statuses VALUES (87, 78, 57, 685, '#000');
INSERT INTO kanban_statuses VALUES (74, 69, 58, 672, '#000');
INSERT INTO kanban_statuses VALUES (88, 69, 58, 686, '#000');
INSERT INTO kanban_statuses VALUES (75, 70, 58, 673, '#000');

SELECT pg_catalog.setval('kanban_statuses_kst_id_seq', 88, true);

INSERT INTO sys_config VALUES (13, 'app_pricing', '0');
INSERT INTO sys_config VALUES (1, 'app_id', '21000182918');
INSERT INTO sys_config VALUES (3, 'app_title', 'WSTEAM');
INSERT INTO sys_config VALUES (4, 'app_version', '2.0.5');
INSERT INTO sys_config VALUES (5, 'app_description', 'Teamwork application based on worksaurus platform');
INSERT INTO sys_config VALUES (6, 'app_keywords', 'team, task, worksaurus, polymer, progressive web');
INSERT INTO sys_config VALUES (7, 'app_author', 'Kreasindo Cipta Teknologi');
INSERT INTO sys_config VALUES (8, 'app_repository', '');
INSERT INTO sys_config VALUES (9, 'app_token', '66c3ff424414b74560788b53434baf309a7b510c5a4ec33f65932671af73f2a5');
INSERT INTO sys_config VALUES (15, 'notif_global', 'You are on Free Package. <a  href="billing"><b>Upgrade PRO Package </b></a> to unlock more features');
INSERT INTO sys_config VALUES (16, 'app_route_fallback', '/worksheet');
INSERT INTO sys_config VALUES (18, 'app_pricing_pro', '68000');
INSERT INTO sys_config VALUES (10, 'app_package', 'FREE');
INSERT INTO sys_config VALUES (11, 'app_limit', '5');
INSERT INTO sys_config VALUES (12, 'app_package_approval', '1');
INSERT INTO sys_config VALUES (14, 'app_package_desc', 'Free account for your daily needs');

SELECT pg_catalog.setval('sys_config_sc_id_seq', 18, true);

INSERT INTO sys_labels VALUES ('opened', 7, '2017-12-25 04:12:43', '#9c27b0', 13, NULL);
INSERT INTO sys_labels VALUES ('others', 7, '2017-12-21 17:50:35', '#4caf50', 9, NULL);
INSERT INTO sys_labels VALUES ('project', 7, '2017-12-19 18:07:22', '#ffc107', 3, NULL);
INSERT INTO sys_labels VALUES ('issue', 7, '2017-12-25 04:12:10', '#e91e63', 12, NULL);
INSERT INTO sys_labels VALUES ('test', 7, '2018-01-03 17:08:00', '#2196f3', 14, NULL);
INSERT INTO sys_labels VALUES ('finance', 7, '2017-12-19 18:07:18', '#607d8b', 2, NULL);
INSERT INTO sys_labels VALUES ('bugs', 7, '2017-12-24 13:53:18', '#f44336', 11, NULL);
INSERT INTO sys_labels VALUES ('closed', 7, '2018-01-18 23:01:10', 'var(--paper-teal-500)', 15, NULL);
INSERT INTO sys_labels VALUES ('nbwo', 7, '2018-01-26 17:08:36', '', 16, 35);
INSERT INTO sys_labels VALUES ('test', 7, '2018-01-26 17:17:44', '', 17, 35);
INSERT INTO sys_labels VALUES ('test', 7, '2018-01-26 17:17:52', '', 18, 36);
INSERT INTO sys_labels VALUES ('private only', 7, '2018-01-26 17:29:23', '#ffc107', 19, 36);
INSERT INTO sys_labels VALUES ('only on project', 1, '2018-01-29 16:51:51', 'var(--paper-blue-500)', 20, 35);

SELECT pg_catalog.setval('sys_labels_sl_id_seq', 20, true);

INSERT INTO sys_menus VALUES (20, 0, 'Labels', 'label-outline', '/references/labels', 5, 1, 0);
INSERT INTO sys_menus VALUES (3, 0, 'Dashboard', 'dashboard', '/dashboard', 3, 1, 0);
INSERT INTO sys_menus VALUES (7, 0, 'Settings', 'device:usb', '/settings', 7, 1, 0);
INSERT INTO sys_menus VALUES (19, 0, 'Worksheet', 'view-carousel', '/worksheet', 2, 1, 0);
INSERT INTO sys_menus VALUES (1, 0, 'Homepage', 'social:public', '/home', 1, 1, 1);
INSERT INTO sys_menus VALUES (21, 1, 'Sub Homepage', 'account-circle', '/roles', 2, 1, 0);
INSERT INTO sys_menus VALUES (22, 0, 'Billing', 'description', '/billing', 8, 1, 0);

SELECT pg_catalog.setval('sys_menus_smn_id_seq', 22, true);

INSERT INTO sys_modules VALUES (1, 'assets', '1.0.0', 'Assets', 'KCT Team', 'https://github.com/progmodules/assets', '/assets');
INSERT INTO sys_modules VALUES (3, 'application', '1.0.0', 'Application', 'KCT Team', NULL, '/');
INSERT INTO sys_modules VALUES (5, 'home', '1.0.0', 'Homepage', 'KCT Team', NULL, '/home');
INSERT INTO sys_modules VALUES (7, 'roles', '1.0.0', 'Roles', 'KCT Team', NULL, '/roles');
INSERT INTO sys_modules VALUES (8, 'users', '1.0.0', 'Users', 'KCT Team', NULL, '/users');
INSERT INTO sys_modules VALUES (9, 'modules', '1.0.0', 'Modules', 'KCT Team', NULL, '/modules');
INSERT INTO sys_modules VALUES (13, 'dashboard', '1.0.0', 'Dashboard', 'KCT Team', NULL, '/dashboard');
INSERT INTO sys_modules VALUES (19, 'labels', '1.0.0', 'Labels', 'KCT Team', NULL, '/references/labels');
INSERT INTO sys_modules VALUES (2, 'auth', '1.0.0', 'Authentication', 'KCT Team', 'https://github.com/progmodules/auth', '/auth');
INSERT INTO sys_modules VALUES (18, 'worksheet', '1.0.0', 'Worksheet', 'KCT Team', NULL, '/worksheet');
INSERT INTO sys_modules VALUES (17, 'settings', '1.0.0', 'Settings', 'KCT Team', NULL, '/settings');
INSERT INTO sys_modules VALUES (20, 'Billing', '1.0.0', 'Billing', 'KCT Team', NULL, '/billing');

INSERT INTO sys_modules_capabilities VALUES (3, 1, 'download_resource', 'Allow user to download resources (image, file etc.)');
INSERT INTO sys_modules_capabilities VALUES (12, 3, 'manage_app', 'Allow user to manage whole application');
INSERT INTO sys_modules_capabilities VALUES (17, 1, 'download_thumbnail', 'Allow user to download image thumbnail');
INSERT INTO sys_modules_capabilities VALUES (22, 5, 'update_notes', 'Allow user to update welcome notes');
INSERT INTO sys_modules_capabilities VALUES (23, 5, 'update_cover', 'Allow user to update background image');
INSERT INTO sys_modules_capabilities VALUES (24, 3, 'send_email', 'Allow user to send mail');
INSERT INTO sys_modules_capabilities VALUES (16, 2, 'login', 'Allow user to perform login action');
INSERT INTO sys_modules_capabilities VALUES (31, 2, 'login_browser', 'Allow user to login web application');
INSERT INTO sys_modules_capabilities VALUES (32, 2, 'login_mobile', 'Allow user to login mobile');
INSERT INTO sys_modules_capabilities VALUES (33, 18, 'manage_project', 'Allow user to manage project');
INSERT INTO sys_modules_capabilities VALUES (34, 17, 'manage_user', 'Allow user to manage user and role');
INSERT INTO sys_modules_capabilities VALUES (35, 17, 'manage_bpm', 'Allow user to manage business process model');
INSERT INTO sys_modules_capabilities VALUES (36, 17, 'manage_setting', 'Allow user to manage general setting');

SELECT pg_catalog.setval('sys_modules_capabilities_smc_id_seq', 36, true);

SELECT pg_catalog.setval('sys_modules_sm_id_seq', 20, true);

INSERT INTO sys_numbers VALUES (1, 'SALES_TICKET', 36, 5, 'SP', NULL);

SELECT pg_catalog.setval('sys_numbers_sn_id_seq', 1, true);

INSERT INTO sys_permissions VALUES (1, 1, 1);

SELECT pg_catalog.setval('sys_permissions_sp_id_seq', 1, true);

INSERT INTO sys_projects VALUES ('example-project', 'Example Project', NULL, 1, '2018-02-14 20:14:21', 19, 40);
INSERT INTO sys_projects VALUES ('private', 'Private', NULL, 7, '2018-01-20 02:34:17', 19, 36);

SELECT pg_catalog.setval('sys_projects_labels_spl_id_seq', 1, false);

SELECT pg_catalog.setval('sys_projects_sp_id_seq', 40, true);

INSERT INTO sys_projects_users VALUES (20, 7, 12);
INSERT INTO sys_projects_users VALUES (21, 7, 13);
INSERT INTO sys_projects_users VALUES (36, 7, 73);
INSERT INTO sys_projects_users VALUES (40, 1, 77);

SELECT pg_catalog.setval('sys_projects_users_spu_id_seq', 77, true);

INSERT INTO sys_roles VALUES (7, 'General Manager', 'general-manager', NULL, 0, '2017-09-18 09:00:50+00', 'system');
INSERT INTO sys_roles VALUES (16, 'Programmer', 'programmer', 'Role for user programmer', 0, '2018-01-11 10:27:53.096389+00', 'system');
INSERT INTO sys_roles VALUES (4, 'Administator', 'administator', 'Role for administrator user', 1, '2017-05-24 18:48:45+00', 'system');

INSERT INTO sys_roles_kanban VALUES (10, 4, 19, 1);

SELECT pg_catalog.setval('sys_roles_kanban_srk_id_seq', 10, true);

INSERT INTO sys_roles_menus VALUES (16, 16, 21, 0);
INSERT INTO sys_roles_menus VALUES (11, 16, 1, 1);
INSERT INTO sys_roles_menus VALUES (12, 16, 3, 1);
INSERT INTO sys_roles_menus VALUES (15, 16, 20, 1);
INSERT INTO sys_roles_menus VALUES (14, 16, 19, 1);
INSERT INTO sys_roles_menus VALUES (13, 16, 7, 1);
INSERT INTO sys_roles_menus VALUES (17, 16, 22, 1);
INSERT INTO sys_roles_menus VALUES (3, 4, 14, 0);
INSERT INTO sys_roles_menus VALUES (1, 4, 1, 1);
INSERT INTO sys_roles_menus VALUES (4, 4, 3, 1);
INSERT INTO sys_roles_menus VALUES (10, 4, 20, 1);
INSERT INTO sys_roles_menus VALUES (9, 4, 19, 1);
INSERT INTO sys_roles_menus VALUES (8, 4, 7, 1);
INSERT INTO sys_roles_menus VALUES (18, 4, 22, 1);

SELECT pg_catalog.setval('sys_roles_menus_srm_id_seq', 18, true);

INSERT INTO sys_roles_panels VALUES (7, 35, 16, 76, 81, 0);

SELECT pg_catalog.setval('sys_roles_panels_srs_id_seq', 7, true);

INSERT INTO sys_roles_permissions VALUES (31, 16, 31, 1);
INSERT INTO sys_roles_permissions VALUES (30, 16, 32, 1);
INSERT INTO sys_roles_permissions VALUES (34, 16, 33, 1);
INSERT INTO sys_roles_permissions VALUES (35, 16, 34, 1);
INSERT INTO sys_roles_permissions VALUES (36, 16, 35, 1);
INSERT INTO sys_roles_permissions VALUES (37, 16, 36, 1);
INSERT INTO sys_roles_permissions VALUES (23, 4, 21, 0);
INSERT INTO sys_roles_permissions VALUES (24, 4, 26, 0);
INSERT INTO sys_roles_permissions VALUES (32, 4, 32, 0);
INSERT INTO sys_roles_permissions VALUES (26, 4, 28, 0);
INSERT INTO sys_roles_permissions VALUES (27, 4, 29, 0);
INSERT INTO sys_roles_permissions VALUES (28, 4, 30, 0);
INSERT INTO sys_roles_permissions VALUES (19, 4, 20, 0);
INSERT INTO sys_roles_permissions VALUES (25, 4, 27, 0);
INSERT INTO sys_roles_permissions VALUES (12, 4, 24, 0);
INSERT INTO sys_roles_permissions VALUES (13, 4, 12, 0);
INSERT INTO sys_roles_permissions VALUES (14, 4, 3, 0);
INSERT INTO sys_roles_permissions VALUES (15, 4, 18, 0);
INSERT INTO sys_roles_permissions VALUES (16, 4, 19, 0);
INSERT INTO sys_roles_permissions VALUES (17, 4, 22, 0);
INSERT INTO sys_roles_permissions VALUES (20, 4, 25, 0);
INSERT INTO sys_roles_permissions VALUES (21, 4, 17, 0);
INSERT INTO sys_roles_permissions VALUES (22, 4, 16, 0);
INSERT INTO sys_roles_permissions VALUES (18, 4, 23, 1);
INSERT INTO sys_roles_permissions VALUES (29, 4, 31, 1);
INSERT INTO sys_roles_permissions VALUES (33, 4, 33, 1);
INSERT INTO sys_roles_permissions VALUES (38, 4, 34, 1);
INSERT INTO sys_roles_permissions VALUES (39, 4, 35, 1);
INSERT INTO sys_roles_permissions VALUES (40, 4, 36, 1);

SELECT pg_catalog.setval('sys_roles_permissions_srp_id_seq', 40, true);

SELECT pg_catalog.setval('sys_roles_sr_id_seq', 16, true);

INSERT INTO sys_users VALUES (7, 4, 'said@kct.co.id', '$2y$08$swEfrnafombDpMRGinkU2u2NsCet8xnfpuZdoeF90cMrpWgZ5TyHS', 'Said M Fahmi', '1757649-me_avatar_big.png', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTg1NDM5ODksImp0aSI6IlJGOVYrTHRvVDlDOFJReHFyQ2hsdUJLcnowSFZ6RmYwM3J2YjIxbXpOdnM9IiwiaXNzIjoiS3JlYXNpbmRvIENpcHRhIFRla25vbG9naSIsIm5iZiI6MTUxODU0Mzk5MCwiZXhwIjoxNTE4NjI3OTkwLCJkYXRhIjp7InN1X2VtYWlsIjoic2FpZEBrY3QuY28uaWQiLCJzdV9zcl9pZCI6NH19.iyk5asW0_QbP8So0HzP-nRfxvkAfvHQCVzSn94raQNl39LetxxCk8t8Vu1sHCuY1cfxC9uQx0cfdAkWME7DsdA', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTg1NDM5ODksImp0aSI6ImMzdHoyVEhoNTQ2UmNYM0FIcGFkZEVwMEFhRnRCdDA2ZkJFTlpNT01rZjQ9IiwiaXNzIjoiS3JlYXNpbmRvIENpcHRhIFRla25vbG9naSIsIm5iZiI6MTUxODU0Mzk5MCwiZXhwIjoxNTE4NjUxOTkwLCJkYXRhIjpudWxsfQ.ulYRWJbOMqrfpuA5CTR0ced_7Bv6Oh6_frs-VZWH5WIaJcCYuSrGHuDJ2o0ys0R4pL1-DH683Z0v2dH8Y9c0gQ', 'Male', NULL, NULL, NULL, 1, '2017-08-04 15:12:15+00', 'system', NULL, NULL);
INSERT INTO sys_users VALUES (8, 4, 'test@yahoo.com', NULL, 'Test User', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, '2017-10-25 11:24:33+00', 'system', NULL, NULL);
INSERT INTO sys_users VALUES (4, 4, 'wili@kct.co.id', '$2y$08$JD1lu23K6va7KJlh7x1o6uMN55nAUsQjFUFcH9o8AFPTitLBS8Y0y', 'Wiliarko M.', 'defaults/avatar-0.jpg', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0OTM4ODMzMjEsImp0aSI6IlhYaERLUTBSUmtweVBKVEJYbkJNQ3ZaZzU1UnRBWXpiVUVFQkFldEZRZVU9IiwiaXNzIjoiS3JlYXNpbmRvIENpcHRhIFRla25vbG9naSIsIm5iZiI6MTQ5Mzg4MzMyMiwiZXhwIjoxNDkzOTY3MzIyLCJkYXRhIjp7InN1X2VtYWlsIjoiam9obk', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0OTM4ODMzMjEsImp0aSI6IktjdFwvRG5pWWZVUlRPY2lTZ1VJVEEwNHFIXC91dlJvYW9cL05vcVJaUmNUanc9IiwiaXNzIjoiS3JlYXNpbmRvIENpcHRhIFRla25vbG9naSIsIm5iZiI6MTQ5Mzg4MzMyMiwiZXhwIjoxNDkzOTkxMzIyLCJkYXRhIjpudWxsfQ.rQO76C44g9N', 'Female', NULL, NULL, NULL, 1, '2017-05-04 07:20:15+00', 'system', NULL, NULL);
INSERT INTO sys_users VALUES (2, 4, 'vidi@kct.co.id', '$2y$08$MVg1UndEeTVGRWJrL1BxS.DUf6A5rt36rq9CByczAtresY5PvVykO', 'Vidi Meylan', 'defaults/avatar-0.jpg', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0OTM4NzczNTUsImp0aSI6ImJaTzgzMFZnTmMzOXlpaGc5Wk02WlBlNTR3WURlK0pjVEZYbytVelFDOFU9IiwiaXNzIjoiS3JlYXNpbmRvIENpcHRhIFRla25vbG9naSIsIm5iZiI6MTQ5Mzg3NzM1NiwiZXhwIjoxNDkzOTYxMzU2LCJkYXRhIjp7InN1X2VtYWlsIjoidmlkaU', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE0OTM4NzczNTUsImp0aSI6ImF3VXJ6cFIrVGJqaGNyMDJGZ01wNENhSUxOSmpUdTVSTXhFXC9HUTMydlEwPSIsImlzcyI6IktyZWFzaW5kbyBDaXB0YSBUZWtub2xvZ2kiLCJuYmYiOjE0OTM4NzczNTYsImV4cCI6MTQ5Mzk4NTM1NiwiZGF0YSI6bnVsbH0.UjnERoG8tMcfLB', 'Female', NULL, NULL, NULL, 1, '2017-05-04 05:55:15+00', 'system', NULL, NULL);
INSERT INTO sys_users VALUES (3, 4, 'cahya@kct.co.id', '$2y$08$JInjOtkULdYcwjnWPXwUL.V5ZUAn8rANXsYliJDdPXrS9bUnS/QfW', 'Cahya Dyzin', 'SelfAvatar_Blue002.jpg', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTM4MDIxMzcsImp0aSI6Ilp6dXR6SktJaUR3NGNYM0JcL0dcL1dXS280XC9Qb001bmhNd3dPUlNQK1lCcFk9IiwiaXNzIjoiS3JlYXNpbmRvIENpcHRhIFRla25vbG9naSIsIm5iZiI6MTUxMzgwMjEzOCwiZXhwIjoxNTEzODg2MTM4LCJkYXRhIjp7InN1X2VtYWlsIjoiY2FoeWFAa2N0LmNvLmlkIiwic3Vfc3JfaWQiOjR9fQ._ARstWVDFeJw2EGcYYa-ALxPMvC_Kt0AoYBY9l2rI09W1nYaVsVW6z014JvO_iL5iGpv62OjbDWb_ZoT0ps4hw', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTM4MDIxMzcsImp0aSI6InlMNXBqQWFNTXgwNW45SVdoWDB0NHh4Tkx1aXJFVmh4XC9CN0FIN3JcL2w3Yz0iLCJpc3MiOiJLcmVhc2luZG8gQ2lwdGEgVGVrbm9sb2dpIiwibmJmIjoxNTEzODAyMTM4LCJleHAiOjE1MTM5MTAxMzgsImRhdGEiOm51bGx9.OWVzLds1_aPnMyEOaWWKC4mpiFZGlD5j4ZcA9YwyASj9w8yzOVFq2m02Jnh_JdLgOIaKWy_yCk-C80-3SfGrBg', 'Male', NULL, NULL, NULL, 1, '2017-05-04 06:24:39+00', 'system', NULL, NULL);
INSERT INTO sys_users VALUES (20, 4, 'vidi@gmail.com', NULL, 'Vidi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2018-01-03 17:31:00+00', 'system', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTQ5NzU0NjAsImp0aSI6IjFhY3lHQVwva0dJbk9NeDlUbkxwNjBlRzVFeEtibWo0K3N4OEFTVnF2R0lNPSIsImlzcyI6IktyZWFzaW5kbyBDaXB0YSBUZWtub2xvZ2kiLCJuYmYiOjE1MTQ5NzU0NjEsImV4cCI6MTUxNTA1OTQ2MSwiZGF0YSI6eyJzdV9lbWFpbCI6InZpZGlAZ21haWwuY29tIn19.2SLWfxUah_eqfcTtxNQ7uigDnD63YdFDgUkYdBDtdktFqlzTJK9XeavlcPvfL5DBEsn5jpeyQiRVLDi4uB2byg', NULL);
INSERT INTO sys_users VALUES (25, 4, 'ikhsan@kct.co.id', NULL, 'Ikhsan', 'defaults/avatar-0.jpg', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2018-01-11 09:31:59.630937+00', 'system', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTU2NjQzMTQsImp0aSI6ImpXY1ZYaGpkK25jY05NZHhRZjZVRWFYYnJwUzMydFFvNDBwTWlRSjJVSTQ9IiwiaXNzIjoiS3JlYXNpbmRvIENpcHRhIFRla25vbG9naSIsIm5iZiI6MTUxNTY2NDMxNSwiZXhwIjoxNTE1NzQ4MzE1LCJkYXRhIjp7InN1X2VtYWlsIjoiaWtoc2FuQGtjdC5jby5pZCJ9fQ.kgXGn8k52fNSuOAy8C7v9tBCEYfW-_pasV75iYeSdHsOj4d8Wrsd_traVGotFcxz0Jp7QP59Zj0LivlE7jdolQ', NULL);
INSERT INTO sys_users VALUES (29, 4, 'roso.sasongko@gmail.com', NULL, 'Roso Sasongko', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, '2018-01-16 23:37:20+00', 'system', 'eohx2Ie1Gk4wWraxOLdbJpYBYQ1xVUXM6BukTZYJaXy7fpb71UjceRHVRpjjtRkhExOf4kZSbARbR0RfTWpu', NULL);
INSERT INTO sys_users VALUES (1, 16, 'roso@kct.co.id', '$2y$08$dWZ2bnZPZGlZTFRzcFM3O.0H1nj9De85nlg47ErMwBPwhnDzCJlFq', 'Roso Sasongko', 'default-avatar-ginger-guy.png', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTg2MTM5MjEsImp0aSI6ImZNNUpMeTRGMEhabVU5WmpqN00zUncxdHArQWNKYTFPZ21oNGwwQmx3b289IiwiaXNzIjoiS3JlYXNpbmRvIENpcHRhIFRla25vbG9naSIsIm5iZiI6MTUxODYxMzkyMiwiZXhwIjoxNTE4Njk3OTIyLCJkYXRhIjp7InN1X2VtYWlsIjoicm9zb0BrY3QuY28uaWQiLCJzdV9zcl9pZCI6MTZ9fQ.WwLMjwvwwRC8kdxEJN3MnmPD2zuQr0V2xWcc4bYuoysP7A9uheMKbvg55UCw2uwJzFn2ryk7Uv-zW5xyVXiUhw', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTg2MTM5MjEsImp0aSI6InBOUkNaM0V5aU5JSXZkRmlnRzNKM2hwamVwNHFQVnZ2dE1GUTBqc3h3alE9IiwiaXNzIjoiS3JlYXNpbmRvIENpcHRhIFRla25vbG9naSIsIm5iZiI6MTUxODYxMzkyMiwiZXhwIjoxNTE4NzIxOTIyLCJkYXRhIjpudWxsfQ.qib0-x194vqSP6ol7LmlXu7MY-qcXJ9MuotjcVRY2hGoXjwrgGsdCBTcmO1kd4UYe3L9ysAJW1vEtO89Xal6YA', 'Male', '1985-07-03', 'Programmer', 'Kreasindo Cipta Teknologi', 1, '2017-04-27 20:52:36+00', 'system', NULL, 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTU2MjQ1NjgsImp0aSI6ImlJbE1VOXdaUGpmd2ZUbk9hMkdUK0oyQlVNTDZCa09oSkx1VFpJa2pCZVU9IiwiaXNzIjoiS3JlYXNpbmRvIENpcHRhIFRla25vbG9naSIsIm5iZiI6MTUxNTYyNDU2OSwiZXhwIjoxNTE1NzA4NTY5LCJkYXRhIjp7InN1X2VtYWlsIjoicm9zb0BrY3QuY28uaWQifX0.o1iI7ZWpATPgALV2P6Frdv09XcMnuIAWd6lImKyZqQN6Y5xp-_JhHC1nxDppVMEUs9qIBOFc8KNHHpBiYM-KZw');
INSERT INTO sys_users VALUES (33, 4, 'nurfarid8924@gmail.com', '$2y$08$TjZIdEdzMk9EZHVlUFpONOOwXuAgxmHlr6kCflBSQmxs07RdSB9Fa', 'Nurfarid8924', 'defaults/avatar-0.jpg', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTg1OTczMTksImp0aSI6IlpZWlRVbVlmaDRwaWxWYW16bFZNc3B2bkFPVVUza0tKYWQzSzhsXC9GdjZnPSIsImlzcyI6IktyZWFzaW5kbyBDaXB0YSBUZWtub2xvZ2kiLCJuYmYiOjE1MTg1OTczMjAsImV4cCI6MTUxODY4MTMyMCwiZGF0YSI6eyJzdV9lbWFpbCI6Im51cmZhcmlkODkyNEBnbWFpbC5jb20iLCJzdV9zcl9pZCI6NH19.YrGgFOp4aCueu_9CD-Sr5qZVtMBtjg3kTp42Rcm-Bx7U9orNQSWSywY7zp3q9oN20u7T-bY5HlCoKqZO27td7g', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzUxMiJ9.eyJpYXQiOjE1MTg1OTczMTksImp0aSI6IlNCbW9TVkRNXC9Fa0lFVytFaUZJajh2N096MXRNb3JRVGl0TENDNWdQK2lJPSIsImlzcyI6IktyZWFzaW5kbyBDaXB0YSBUZWtub2xvZ2kiLCJuYmYiOjE1MTg1OTczMjAsImV4cCI6MTUxODcwNTMyMCwiZGF0YSI6bnVsbH0.syXhNaIo2jq6IvwSm-wyMFbb3jVOgfqFha6Uo-5Ex4eVM7nARu_Yk5pdkFEDMYV1awF79DxUwon90j8Z-3gMpQ', NULL, NULL, NULL, NULL, 1, '2018-02-14 08:30:35.795819+00', 'system', NULL, NULL);

SELECT pg_catalog.setval('sys_users_kanban_suk_id_seq', 27, true);

SELECT pg_catalog.setval('sys_users_menus_sum_id_seq', 1, true);

SELECT pg_catalog.setval('sys_users_panels_sus_id_seq', 1, false);

SELECT pg_catalog.setval('sys_users_permissions_sup_id_seq', 1, true);

SELECT pg_catalog.setval('sys_users_su_id_seq', 33, true);

INSERT INTO trx_tasks VALUES (104, 'A', 'Doing', 40, '<p><br></p>', '2018-02-15', 1, '2018-02-14 20:21:31', NULL);
INSERT INTO trx_tasks VALUES (106, 'C', 'Todo', 40, '<p><br></p>', '2018-02-15', 1, '2018-02-14 20:21:43', NULL);
INSERT INTO trx_tasks VALUES (105, 'B', 'Done', 40, '<p><br></p>', '2018-02-15', 1, '2018-02-14 20:21:37', NULL);

INSERT INTO trx_tasks_activities VALUES (1035, 104, 'create', 1, '2018-02-14 20:21:31', 'A', NULL);
INSERT INTO trx_tasks_activities VALUES (1036, 105, 'create', 1, '2018-02-14 20:21:37', 'B', NULL);
INSERT INTO trx_tasks_activities VALUES (1037, 106, 'create', 1, '2018-02-14 20:21:43', 'C', NULL);
INSERT INTO trx_tasks_activities VALUES (1038, 106, 'update_flag', 1, '2018-02-14 20:21:47', 'Doing', NULL);
INSERT INTO trx_tasks_activities VALUES (1039, 105, 'update_flag', 1, '2018-02-14 20:21:49', 'Done', NULL);
INSERT INTO trx_tasks_activities VALUES (1040, 104, 'update_flag', 1, '2018-02-14 20:31:03', 'Doing', NULL);
INSERT INTO trx_tasks_activities VALUES (1041, 106, 'update_flag', 1, '2018-02-14 20:31:05', 'Todo', NULL);

SELECT pg_catalog.setval('trx_tasks_activities_tta_id_seq', 1041, true);

SELECT pg_catalog.setval('trx_tasks_comments_ttc_id_seq', 1, false);

SELECT pg_catalog.setval('trx_tasks_labels_ttl_id_seq', 136, true);

INSERT INTO trx_tasks_statuses VALUES (836, 106, 668, 861, 'classic-workflow', 1, '2018-02-14 20:21:43', NULL);
INSERT INTO trx_tasks_statuses VALUES (838, 105, 685, 863, 'classic-workflow', 0, '2018-02-14 20:21:49', NULL);
INSERT INTO trx_tasks_statuses VALUES (835, 105, 668, 861, 'classic-workflow', 1, '2018-02-14 20:21:37', NULL);
INSERT INTO trx_tasks_statuses VALUES (839, 104, 669, 862, 'classic-workflow', 0, '2018-02-14 20:31:03', NULL);
INSERT INTO trx_tasks_statuses VALUES (834, 104, 668, 861, 'classic-workflow', 1, '2018-02-14 20:21:31', NULL);
INSERT INTO trx_tasks_statuses VALUES (840, 106, 683, 861, 'classic-workflow', 0, '2018-02-14 20:31:05', NULL);
INSERT INTO trx_tasks_statuses VALUES (837, 106, 669, 862, 'classic-workflow', 1, '2018-02-14 20:21:48', NULL);

SELECT pg_catalog.setval('trx_tasks_statuses_tts_id_seq', 840, true);

SELECT pg_catalog.setval('trx_tasks_tt_id_seq', 106, true);

SELECT pg_catalog.setval('trx_tasks_users_ttu_id_seq', 105, true);

ALTER TABLE ONLY bpm_diagrams
    ADD CONSTRAINT idx_35836_primary PRIMARY KEY (id);

ALTER TABLE ONLY bpm_forms
    ADD CONSTRAINT idx_35846_primary PRIMARY KEY (bf_id);

ALTER TABLE ONLY bpm_forms_roles
    ADD CONSTRAINT idx_35855_primary PRIMARY KEY (bfr_id);

ALTER TABLE ONLY bpm_forms_users
    ADD CONSTRAINT idx_35861_primary PRIMARY KEY (bfu_id);

ALTER TABLE ONLY bpm_links
    ADD CONSTRAINT idx_35867_primary PRIMARY KEY (id);

ALTER TABLE ONLY bpm_shapes
    ADD CONSTRAINT idx_35879_primary PRIMARY KEY (id);

ALTER TABLE ONLY dx_auth
    ADD CONSTRAINT idx_35888_primary PRIMARY KEY (auth_col_id);

ALTER TABLE ONLY dx_mapping
    ADD CONSTRAINT idx_35894_primary PRIMARY KEY (map_id);

ALTER TABLE ONLY dx_profiles
    ADD CONSTRAINT idx_35909_primary PRIMARY KEY (profile_id);

ALTER TABLE ONLY kanban
    ADD CONSTRAINT idx_35930_primary PRIMARY KEY (panel_id);

ALTER TABLE ONLY kanban_forms
    ADD CONSTRAINT idx_35936_primary PRIMARY KEY (kf_id);

ALTER TABLE ONLY kanban_panels
    ADD CONSTRAINT idx_35945_primary PRIMARY KEY (kp_id);

ALTER TABLE ONLY kanban_settings
    ADD CONSTRAINT idx_35952_primary PRIMARY KEY (ks_id);

ALTER TABLE ONLY kanban_statuses
    ADD CONSTRAINT idx_35958_primary PRIMARY KEY (kst_id);

ALTER TABLE ONLY sys_captcha
    ADD CONSTRAINT idx_35968_primary PRIMARY KEY (id, namespace);

ALTER TABLE ONLY sys_config
    ADD CONSTRAINT idx_35976_primary PRIMARY KEY (sc_id);

ALTER TABLE ONLY sys_menus
    ADD CONSTRAINT idx_35985_primary PRIMARY KEY (smn_id);

ALTER TABLE ONLY sys_modules
    ADD CONSTRAINT idx_35994_primary PRIMARY KEY (sm_id);

ALTER TABLE ONLY sys_modules_capabilities
    ADD CONSTRAINT idx_36004_primary PRIMARY KEY (smc_id);

ALTER TABLE ONLY sys_numbers
    ADD CONSTRAINT idx_36010_primary PRIMARY KEY (sn_id);

ALTER TABLE ONLY sys_permissions
    ADD CONSTRAINT idx_36016_primary PRIMARY KEY (sp_id);

ALTER TABLE ONLY sys_roles
    ADD CONSTRAINT idx_36022_primary PRIMARY KEY (sr_id);

ALTER TABLE ONLY sys_roles_kanban
    ADD CONSTRAINT idx_36034_primary PRIMARY KEY (srk_id);

ALTER TABLE ONLY sys_roles_menus
    ADD CONSTRAINT idx_36041_primary PRIMARY KEY (srm_id);

ALTER TABLE ONLY sys_roles_permissions
    ADD CONSTRAINT idx_36048_primary PRIMARY KEY (srp_id);

ALTER TABLE ONLY sys_users
    ADD CONSTRAINT idx_36055_primary PRIMARY KEY (su_id);

ALTER TABLE ONLY sys_users_kanban
    ADD CONSTRAINT idx_36067_primary PRIMARY KEY (suk_id);

ALTER TABLE ONLY sys_users_menus
    ADD CONSTRAINT idx_36074_primary PRIMARY KEY (sum_id);

ALTER TABLE ONLY sys_users_permissions
    ADD CONSTRAINT idx_36081_primary PRIMARY KEY (sup_id);

ALTER TABLE ONLY trx_tasks
    ADD CONSTRAINT idx_36153_primary PRIMARY KEY (tt_id);

ALTER TABLE ONLY sys_labels
    ADD CONSTRAINT sys_labels_pkey PRIMARY KEY (sl_id);

ALTER TABLE ONLY sys_projects_labels
    ADD CONSTRAINT sys_projects_labels_pkey PRIMARY KEY (spl_id);

ALTER TABLE ONLY sys_projects
    ADD CONSTRAINT sys_projects_pkey PRIMARY KEY (sp_id);

ALTER TABLE ONLY sys_projects_users
    ADD CONSTRAINT sys_projects_users_pkey PRIMARY KEY (spu_id);

ALTER TABLE ONLY sys_roles_panels
    ADD CONSTRAINT sys_roles_panels_pkey PRIMARY KEY (srs_id);

ALTER TABLE ONLY sys_users_panels
    ADD CONSTRAINT sys_users_panels_pkey PRIMARY KEY (sus_id);

ALTER TABLE ONLY trx_tasks_activities
    ADD CONSTRAINT trx_tasks_activities_pkey PRIMARY KEY (tta_id);

ALTER TABLE ONLY trx_tasks_comments
    ADD CONSTRAINT trx_tasks_comments_pkey PRIMARY KEY (ttc_id);

ALTER TABLE ONLY trx_tasks_labels
    ADD CONSTRAINT trx_tasks_labels_pkey PRIMARY KEY (ttl_id);

ALTER TABLE ONLY trx_tasks_statuses
    ADD CONSTRAINT trx_tasks_statuses_pkey PRIMARY KEY (tts_id);

ALTER TABLE ONLY trx_tasks_users
    ADD CONSTRAINT trx_tasks_users_pkey PRIMARY KEY (ttu_id);

CREATE INDEX idx_35968_created ON sys_captcha USING btree (created);

CREATE INDEX trx_tasks_statuses_tts_status_idx ON trx_tasks_statuses USING btree (tts_status);

CREATE INDEX trx_tasks_tt_sp_id_idx ON trx_tasks USING btree (tt_sp_id);

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;

