-- Cooca ERP Import-Export: skema database (PostgreSQL 15+)
-- Dibuat 12 Oktober 2026, draft v1.0. Status/enum disimpan sebagai text dan divalidasi di aplikasi.
create extension if not exists pgcrypto;


-- 1. Platform: akun, hak akses, audit, master (F0)
create table roles (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text,
  description text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table permissions (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  module text,
  action text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table role_permissions (
  id uuid primary key default gen_random_uuid(),
  role_id uuid not null,
  perm_id uuid not null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table users (
  id uuid primary key default gen_random_uuid(),
  email text not null unique,
  name text not null,
  password_hash text,
  twofa_secret text,
  status text not null,
  last_login_at timestamptz,
  employee_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table user_roles (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null,
  role_id uuid not null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table client_assignments (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null,
  client_id uuid not null,
  process_code text,
  is_backup boolean,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table audit_logs (
  id uuid primary key default gen_random_uuid(),
  actor_id uuid,
  actor_type text not null,
  action text not null,
  object_type text not null,
  object_id uuid,
  old_value jsonb,
  new_value jsonb,
  ip text,
  created_at timestamptz not null default now()
);
create table system_settings (
  id uuid primary key default gen_random_uuid(),
  key text not null unique,
  value jsonb not null,
  description text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table holidays (
  id uuid primary key default gen_random_uuid(),
  date date not null unique,
  name text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table integration_accounts (
  id uuid primary key default gen_random_uuid(),
  provider text not null,
  client_id uuid,
  label text,
  secret_ref text not null,
  token_expires_at timestamptz,
  status text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table countries (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text not null,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table currencies (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text not null,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table exchange_rates (
  id uuid primary key default gen_random_uuid(),
  currency_id uuid not null,
  rate numeric(18,2) not null,
  source text,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table ports (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text not null,
  country_id uuid,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table customs_offices (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text not null,
  port_id uuid,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table incoterms (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table container_types (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text not null,
  size_ft integer,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table hold_types (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text not null,
  default_action text,
  default_sla_hours integer,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- 2. Klien, shipment, dokumen (F1)
create table clients (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  nib text,
  api_no text,
  npwp text,
  address text,
  status text not null,
  portal_enabled boolean,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table client_contacts (
  id uuid primary key default gen_random_uuid(),
  client_id uuid not null,
  name text not null,
  phone text,
  email text,
  role_label text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table client_warehouses (
  id uuid primary key default gen_random_uuid(),
  client_id uuid not null,
  name text not null,
  address text,
  city text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table client_poa (
  id uuid primary key default gen_random_uuid(),
  client_id uuid not null,
  number text not null,
  valid_from date,
  valid_to date not null,
  file_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table shipments (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  client_id uuid not null,
  direction text not null,
  declaration_type text,
  bl_no text,
  eta timestamptz,
  port_id uuid,
  incoterm_id uuid,
  status text not null,
  closed_at timestamptz,
  created_by_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table shipment_pic (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  process_code text not null,
  user_id uuid not null,
  backup_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table shipment_events (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  process_code text,
  from_status text,
  to_status text not null,
  actor_id uuid,
  note text,
  created_at timestamptz not null default now()
);
create table documents (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  doc_type text not null,
  process_code text,
  version integer not null,
  file_key text not null,
  file_name text,
  status text not null,
  reject_reason text,
  uploaded_by text,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table shipment_required_documents (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  doc_type text not null,
  status text not null,
  source text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table document_validations (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  check_type text not null,
  diff jsonb,
  noted_by_id uuid,
  note text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- 3. Tim Document (F2, F3)
create table shipment_items (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  line_no integer,
  name text,
  material text,
  spec text,
  qty numeric(10,4),
  weight numeric(10,4),
  origin_country_id uuid,
  hs_code_id uuid,
  hs_approved_by_id uuid,
  hs_approved_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table item_hs_suggestions (
  id uuid primary key default gen_random_uuid(),
  item_id uuid not null,
  hs_code_id uuid not null,
  score numeric(10,4),
  reason text,
  rank integer,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table hs_codes (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  description text not null,
  duty_bm numeric(10,4),
  btki text,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table hs_regulations (
  id uuid primary key default gen_random_uuid(),
  hs_from text,
  hs_to text,
  origin_country_id uuid,
  doc_condition jsonb,
  required_doc text not null,
  source_regulation text,
  verified boolean,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table client_product_master (
  id uuid primary key default gen_random_uuid(),
  client_id uuid not null,
  product_name text not null,
  hs_code_id uuid not null,
  source text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table document_types (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text not null,
  header_schema jsonb,
  item_schema jsonb,
  checklist jsonb,
  approval_flow jsonb,
  mapping_api jsonb,
  version integer,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table shipment_declarations (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  doc_type_id uuid not null,
  revision integer not null,
  status text not null,
  payload jsonb,
  aju_no text,
  nopen text,
  channel text,
  submitted_at timestamptz,
  approved_by_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table customs_billings (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  kind text not null,
  amount numeric(18,2) not null,
  status text not null,
  paid_at timestamptz,
  proof_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table outbound_jobs (
  id uuid primary key default gen_random_uuid(),
  provider text not null,
  idempotency_key text not null unique,
  shipment_id uuid,
  payload jsonb,
  status text not null,
  attempts integer,
  next_retry_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table api_sync_logs (
  id uuid primary key default gen_random_uuid(),
  provider text not null,
  shipment_id uuid,
  job_id uuid,
  request jsonb,
  response jsonb,
  http_status integer,
  success boolean,
  created_at timestamptz not null default now()
);

-- 4. Delivery Order (F4)
create table carriers (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text not null,
  email text,
  tracking_provider text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table shipment_voyages (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  carrier_id uuid not null,
  vessel text,
  voyage text,
  etd timestamptz,
  eta timestamptz,
  pol_id uuid,
  pod_id uuid,
  free_time_days integer,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table shipment_containers (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  voyage_id uuid,
  number text not null,
  type_id uuid,
  seal_no text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table tracking_events (
  id uuid primary key default gen_random_uuid(),
  voyage_id uuid not null,
  source text,
  event text,
  position jsonb,
  eta timestamptz,
  created_at timestamptz not null default now()
);
create table carrier_emails (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  carrier_id uuid,
  thread_id text,
  direction text,
  subject text,
  body text,
  sent_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table carrier_invoices (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  carrier_id uuid not null,
  number text,
  amount numeric(18,2) not null,
  currency_id uuid,
  due_date date,
  status text not null,
  tariff_diff numeric(18,2),
  approved_by_id uuid,
  paid_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table do_validity_events (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  status text not null,
  valid_to date,
  extension_cost numeric(18,2),
  note text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- 5. Traffic dan lapangan (F5)
create table shipment_holds (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  hold_type_id uuid not null,
  status text not null,
  due_at timestamptz,
  pic_id uuid,
  note text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table shipment_hold_steps (
  id uuid primary key default gen_random_uuid(),
  hold_id uuid not null,
  seq integer not null,
  name text not null,
  status text not null,
  pic_id uuid,
  done_at timestamptz,
  evidence_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table field_inspections (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  hold_id uuid,
  inspector_id uuid,
  result text,
  notes text,
  report_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table delivery_vendors (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  phone text,
  status text not null,
  score numeric(10,4),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table vendor_rates (
  id uuid primary key default gen_random_uuid(),
  vendor_id uuid not null,
  origin text,
  destination text,
  container_size integer,
  rate numeric(18,2) not null,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table vendor_quotes (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  vendor_id uuid not null,
  amount numeric(18,2) not null,
  selected boolean,
  reason text,
  approved_by_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table deliveries (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  vendor_id uuid,
  truck_plate text,
  driver_name text,
  dispatch_note_id uuid,
  status text not null,
  eta timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table delivery_checkpoints (
  id uuid primary key default gen_random_uuid(),
  delivery_id uuid not null,
  location text,
  lat numeric(10,4),
  lng numeric(10,4),
  note text,
  source text,
  created_at timestamptz not null default now()
);
create table pod_records (
  id uuid primary key default gen_random_uuid(),
  delivery_id uuid not null,
  receiver text,
  received_at timestamptz,
  proof_id uuid,
  closed_confirmed_by_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table claims (
  id uuid primary key default gen_random_uuid(),
  delivery_id uuid not null,
  vendor_id uuid,
  kind text not null,
  amount numeric(18,2),
  status text not null,
  note text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table container_returns (
  id uuid primary key default gen_random_uuid(),
  container_id uuid not null,
  return_due date,
  returned_at timestamptz,
  cost numeric(18,2),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- 6. SLA, notifikasi, KPI (F6)
create table sla_policies (
  id uuid primary key default gen_random_uuid(),
  process_code text not null,
  client_id uuid,
  doc_type text,
  target_hours numeric(10,4) not null,
  warn_pct integer,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table sla_timers (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  process_code text not null,
  started_at timestamptz not null,
  due_at timestamptz not null,
  completed_at timestamptz,
  status text not null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table notifications (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null,
  kind text not null,
  channel text not null,
  shipment_id uuid,
  title text,
  body text,
  read_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table notification_prefs (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null,
  kind text not null,
  channel text not null,
  enabled boolean,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table kpi_snapshots (
  id uuid primary key default gen_random_uuid(),
  scope text not null,
  subject_id uuid,
  period text not null,
  metrics jsonb not null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- 7. Keuangan (F7)
create table chart_of_accounts (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text not null,
  type text not null,
  parent_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table cost_items (
  id uuid primary key default gen_random_uuid(),
  shipment_id uuid not null,
  category text not null,
  account_id uuid,
  source_type text,
  source_id uuid,
  amount numeric(18,2) not null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table client_invoices (
  id uuid primary key default gen_random_uuid(),
  number text not null unique,
  client_id uuid not null,
  shipment_id uuid,
  status text not null,
  issued_at date,
  due_date date,
  total numeric(18,2) not null,
  received_at date,
  approved_by_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table client_invoice_lines (
  id uuid primary key default gen_random_uuid(),
  invoice_id uuid not null,
  description text not null,
  amount numeric(18,2) not null,
  cost_item_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table vendor_bills (
  id uuid primary key default gen_random_uuid(),
  vendor_type text not null,
  shipment_id uuid,
  ref_no text,
  amount numeric(18,2) not null,
  due_date date,
  status text not null,
  approved_by_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table vendor_payments (
  id uuid primary key default gen_random_uuid(),
  bill_id uuid not null,
  account_id uuid not null,
  amount numeric(18,2) not null,
  paid_at date,
  proof_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table cash_bank_accounts (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  bank text,
  account_no text,
  currency_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table cash_bank_transactions (
  id uuid primary key default gen_random_uuid(),
  account_id uuid not null,
  trx_date date not null,
  amount numeric(18,2) not null,
  description text,
  reconciled boolean,
  ref_type text,
  ref_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table journal_entries (
  id uuid primary key default gen_random_uuid(),
  entry_date date not null,
  source_type text,
  source_id uuid,
  memo text,
  posted_by_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table journal_lines (
  id uuid primary key default gen_random_uuid(),
  entry_id uuid not null,
  account_id uuid not null,
  debit numeric(18,2),
  credit numeric(18,2),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table approvals (
  id uuid primary key default gen_random_uuid(),
  object_type text not null,
  object_id uuid not null,
  step integer not null,
  approver_role text,
  status text not null,
  decided_by_id uuid,
  decided_at timestamptz,
  reason text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- 8. HRM (F8)
create table employees (
  id uuid primary key default gen_random_uuid(),
  nik text not null unique,
  full_name text not null,
  position text,
  team text,
  start_date date,
  status text not null,
  base_salary numeric(18,2),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table employment_contracts (
  id uuid primary key default gen_random_uuid(),
  employee_id uuid not null,
  kind text not null,
  number text,
  start_date date,
  end_date date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table employee_documents (
  id uuid primary key default gen_random_uuid(),
  employee_id uuid not null,
  doc_type text not null,
  file_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table assets (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  name text not null,
  status text not null,
  purchase_value numeric(18,2),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table asset_assignments (
  id uuid primary key default gen_random_uuid(),
  asset_id uuid not null,
  employee_id uuid not null,
  assigned_at date,
  returned_at date,
  handover_proof_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table work_locations (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  lat numeric(10,4),
  lng numeric(10,4),
  radius_m integer,
  valid_from date,
  valid_to date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table shifts (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  start_time text,
  end_time text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table biometric_consents (
  id uuid primary key default gen_random_uuid(),
  employee_id uuid not null,
  consent_at timestamptz not null,
  revoked_at timestamptz,
  retention_until date,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table attendance_logs (
  id uuid primary key default gen_random_uuid(),
  employee_id uuid not null,
  kind text not null,
  at timestamptz not null,
  lat numeric(10,4),
  lng numeric(10,4),
  location_id uuid,
  face_match numeric(10,4),
  valid boolean,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table leave_requests (
  id uuid primary key default gen_random_uuid(),
  employee_id uuid not null,
  kind text not null,
  from_date date not null,
  to_date date not null,
  status text not null,
  approver_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table leave_balances (
  id uuid primary key default gen_random_uuid(),
  employee_id uuid not null,
  year integer not null,
  kind text not null,
  balance numeric(10,4),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table overtime_requests (
  id uuid primary key default gen_random_uuid(),
  employee_id uuid not null,
  work_date date not null,
  hours numeric(10,4) not null,
  status text not null,
  approver_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table cash_advances (
  id uuid primary key default gen_random_uuid(),
  employee_id uuid not null,
  amount numeric(18,2) not null,
  installments integer,
  balance numeric(18,2),
  status text not null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table payroll_runs (
  id uuid primary key default gen_random_uuid(),
  period text not null unique,
  status text not null,
  approved_by_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table payslips (
  id uuid primary key default gen_random_uuid(),
  run_id uuid not null,
  employee_id uuid not null,
  gross numeric(18,2),
  net numeric(18,2),
  deductions numeric(18,2),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table payslip_lines (
  id uuid primary key default gen_random_uuid(),
  payslip_id uuid not null,
  label text not null,
  kind text not null,
  amount numeric(18,2) not null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- 9. CRM, portal, mobile (F9-F11)
create table crm_channels (
  id uuid primary key default gen_random_uuid(),
  kind text not null,
  label text,
  external_id text,
  status text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table crm_contacts (
  id uuid primary key default gen_random_uuid(),
  client_id uuid,
  name text not null,
  phone text,
  handle text,
  opt_in boolean,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table crm_conversations (
  id uuid primary key default gen_random_uuid(),
  contact_id uuid not null,
  channel_id uuid not null,
  assignee_id uuid,
  status text not null,
  last_message_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table crm_messages (
  id uuid primary key default gen_random_uuid(),
  conversation_id uuid not null,
  direction text not null,
  body text,
  template_id uuid,
  sent_by text not null,
  ai_generated boolean,
  created_at timestamptz not null default now()
);
create table crm_templates (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  language text,
  body text,
  meta_status text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table crm_pipeline_stages (
  id uuid primary key default gen_random_uuid(),
  name text not null,
  seq integer not null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table crm_deals (
  id uuid primary key default gen_random_uuid(),
  contact_id uuid not null,
  stage_id uuid not null,
  value numeric(18,2),
  owner_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table client_portal_tokens (
  id uuid primary key default gen_random_uuid(),
  client_id uuid not null,
  token_hash text not null unique,
  expires_at timestamptz,
  revoked_at timestamptz,
  last_used_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table client_portal_activity (
  id uuid primary key default gen_random_uuid(),
  token_id uuid not null,
  action text not null,
  shipment_id uuid,
  ip text,
  device text,
  created_at timestamptz not null default now()
);
create table device_tokens (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null,
  platform text not null,
  push_token text not null unique,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- 10. AI Agent, MCP, migrasi (F12-F13)
create table mcp_credentials (
  id uuid primary key default gen_random_uuid(),
  user_id uuid,
  scope_roles jsonb not null,
  token_hash text not null unique,
  revoked_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table mcp_call_logs (
  id uuid primary key default gen_random_uuid(),
  credential_id uuid,
  tool text not null,
  args jsonb,
  result_status text,
  provider text,
  created_at timestamptz not null default now()
);
create table ai_action_queue (
  id uuid primary key default gen_random_uuid(),
  kind text not null,
  proposal jsonb not null,
  shipment_id uuid,
  status text not null,
  decided_by_id uuid,
  reason text,
  executed_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table ai_reply_rules (
  id uuid primary key default gen_random_uuid(),
  topic text not null,
  do_rules text,
  dont_rules text,
  escalate boolean,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table ai_daily_reports (
  id uuid primary key default gen_random_uuid(),
  report_date date not null unique,
  content jsonb not null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table import_jobs (
  id uuid primary key default gen_random_uuid(),
  kind text not null,
  file_id uuid,
  status text not null,
  total_rows integer,
  ok_rows integer,
  error_rows integer,
  created_by_id uuid,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create table import_errors (
  id uuid primary key default gen_random_uuid(),
  job_id uuid not null,
  row_no integer,
  message text,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

-- Foreign key (ditambahkan setelah semua tabel ada)
alter table role_permissions add constraint fk_role_permissions_role_id foreign key (role_id) references roles(id);
alter table role_permissions add constraint fk_role_permissions_perm_id foreign key (perm_id) references permissions(id);
alter table users add constraint fk_users_employee_id foreign key (employee_id) references employees(id);
alter table user_roles add constraint fk_user_roles_user_id foreign key (user_id) references users(id);
alter table user_roles add constraint fk_user_roles_role_id foreign key (role_id) references roles(id);
alter table client_assignments add constraint fk_client_assignments_user_id foreign key (user_id) references users(id);
alter table client_assignments add constraint fk_client_assignments_client_id foreign key (client_id) references clients(id);
alter table audit_logs add constraint fk_audit_logs_actor_id foreign key (actor_id) references users(id);
alter table integration_accounts add constraint fk_integration_accounts_client_id foreign key (client_id) references clients(id);
alter table exchange_rates add constraint fk_exchange_rates_currency_id foreign key (currency_id) references currencies(id);
alter table ports add constraint fk_ports_country_id foreign key (country_id) references countries(id);
alter table customs_offices add constraint fk_customs_offices_port_id foreign key (port_id) references ports(id);
alter table client_contacts add constraint fk_client_contacts_client_id foreign key (client_id) references clients(id);
alter table client_warehouses add constraint fk_client_warehouses_client_id foreign key (client_id) references clients(id);
alter table client_poa add constraint fk_client_poa_client_id foreign key (client_id) references clients(id);
alter table client_poa add constraint fk_client_poa_file_id foreign key (file_id) references documents(id);
alter table shipments add constraint fk_shipments_client_id foreign key (client_id) references clients(id);
alter table shipments add constraint fk_shipments_port_id foreign key (port_id) references ports(id);
alter table shipments add constraint fk_shipments_incoterm_id foreign key (incoterm_id) references incoterms(id);
alter table shipments add constraint fk_shipments_created_by_id foreign key (created_by_id) references users(id);
alter table shipment_pic add constraint fk_shipment_pic_shipment_id foreign key (shipment_id) references shipments(id);
alter table shipment_pic add constraint fk_shipment_pic_user_id foreign key (user_id) references users(id);
alter table shipment_pic add constraint fk_shipment_pic_backup_id foreign key (backup_id) references users(id);
alter table shipment_events add constraint fk_shipment_events_shipment_id foreign key (shipment_id) references shipments(id);
alter table shipment_events add constraint fk_shipment_events_actor_id foreign key (actor_id) references users(id);
alter table documents add constraint fk_documents_shipment_id foreign key (shipment_id) references shipments(id);
alter table shipment_required_documents add constraint fk_shipment_required_documents_shipment_id foreign key (shipment_id) references shipments(id);
alter table document_validations add constraint fk_document_validations_shipment_id foreign key (shipment_id) references shipments(id);
alter table document_validations add constraint fk_document_validations_noted_by_id foreign key (noted_by_id) references users(id);
alter table shipment_items add constraint fk_shipment_items_shipment_id foreign key (shipment_id) references shipments(id);
alter table shipment_items add constraint fk_shipment_items_origin_country_id foreign key (origin_country_id) references countries(id);
alter table shipment_items add constraint fk_shipment_items_hs_code_id foreign key (hs_code_id) references hs_codes(id);
alter table shipment_items add constraint fk_shipment_items_hs_approved_by_id foreign key (hs_approved_by_id) references users(id);
alter table item_hs_suggestions add constraint fk_item_hs_suggestions_item_id foreign key (item_id) references shipment_items(id);
alter table item_hs_suggestions add constraint fk_item_hs_suggestions_hs_code_id foreign key (hs_code_id) references hs_codes(id);
alter table hs_regulations add constraint fk_hs_regulations_origin_country_id foreign key (origin_country_id) references countries(id);
alter table client_product_master add constraint fk_client_product_master_client_id foreign key (client_id) references clients(id);
alter table client_product_master add constraint fk_client_product_master_hs_code_id foreign key (hs_code_id) references hs_codes(id);
alter table shipment_declarations add constraint fk_shipment_declarations_shipment_id foreign key (shipment_id) references shipments(id);
alter table shipment_declarations add constraint fk_shipment_declarations_doc_type_id foreign key (doc_type_id) references document_types(id);
alter table shipment_declarations add constraint fk_shipment_declarations_approved_by_id foreign key (approved_by_id) references users(id);
alter table customs_billings add constraint fk_customs_billings_shipment_id foreign key (shipment_id) references shipments(id);
alter table customs_billings add constraint fk_customs_billings_proof_id foreign key (proof_id) references documents(id);
alter table outbound_jobs add constraint fk_outbound_jobs_shipment_id foreign key (shipment_id) references shipments(id);
alter table api_sync_logs add constraint fk_api_sync_logs_shipment_id foreign key (shipment_id) references shipments(id);
alter table api_sync_logs add constraint fk_api_sync_logs_job_id foreign key (job_id) references outbound_jobs(id);
alter table shipment_voyages add constraint fk_shipment_voyages_shipment_id foreign key (shipment_id) references shipments(id);
alter table shipment_voyages add constraint fk_shipment_voyages_carrier_id foreign key (carrier_id) references carriers(id);
alter table shipment_voyages add constraint fk_shipment_voyages_pol_id foreign key (pol_id) references ports(id);
alter table shipment_voyages add constraint fk_shipment_voyages_pod_id foreign key (pod_id) references ports(id);
alter table shipment_containers add constraint fk_shipment_containers_shipment_id foreign key (shipment_id) references shipments(id);
alter table shipment_containers add constraint fk_shipment_containers_voyage_id foreign key (voyage_id) references shipment_voyages(id);
alter table shipment_containers add constraint fk_shipment_containers_type_id foreign key (type_id) references container_types(id);
alter table tracking_events add constraint fk_tracking_events_voyage_id foreign key (voyage_id) references shipment_voyages(id);
alter table carrier_emails add constraint fk_carrier_emails_shipment_id foreign key (shipment_id) references shipments(id);
alter table carrier_emails add constraint fk_carrier_emails_carrier_id foreign key (carrier_id) references carriers(id);
alter table carrier_invoices add constraint fk_carrier_invoices_shipment_id foreign key (shipment_id) references shipments(id);
alter table carrier_invoices add constraint fk_carrier_invoices_carrier_id foreign key (carrier_id) references carriers(id);
alter table carrier_invoices add constraint fk_carrier_invoices_currency_id foreign key (currency_id) references currencies(id);
alter table carrier_invoices add constraint fk_carrier_invoices_approved_by_id foreign key (approved_by_id) references users(id);
alter table do_validity_events add constraint fk_do_validity_events_shipment_id foreign key (shipment_id) references shipments(id);
alter table shipment_holds add constraint fk_shipment_holds_shipment_id foreign key (shipment_id) references shipments(id);
alter table shipment_holds add constraint fk_shipment_holds_hold_type_id foreign key (hold_type_id) references hold_types(id);
alter table shipment_holds add constraint fk_shipment_holds_pic_id foreign key (pic_id) references users(id);
alter table shipment_hold_steps add constraint fk_shipment_hold_steps_hold_id foreign key (hold_id) references shipment_holds(id);
alter table shipment_hold_steps add constraint fk_shipment_hold_steps_pic_id foreign key (pic_id) references users(id);
alter table shipment_hold_steps add constraint fk_shipment_hold_steps_evidence_id foreign key (evidence_id) references documents(id);
alter table field_inspections add constraint fk_field_inspections_shipment_id foreign key (shipment_id) references shipments(id);
alter table field_inspections add constraint fk_field_inspections_hold_id foreign key (hold_id) references shipment_holds(id);
alter table field_inspections add constraint fk_field_inspections_inspector_id foreign key (inspector_id) references users(id);
alter table field_inspections add constraint fk_field_inspections_report_id foreign key (report_id) references documents(id);
alter table vendor_rates add constraint fk_vendor_rates_vendor_id foreign key (vendor_id) references delivery_vendors(id);
alter table vendor_quotes add constraint fk_vendor_quotes_shipment_id foreign key (shipment_id) references shipments(id);
alter table vendor_quotes add constraint fk_vendor_quotes_vendor_id foreign key (vendor_id) references delivery_vendors(id);
alter table vendor_quotes add constraint fk_vendor_quotes_approved_by_id foreign key (approved_by_id) references users(id);
alter table deliveries add constraint fk_deliveries_shipment_id foreign key (shipment_id) references shipments(id);
alter table deliveries add constraint fk_deliveries_vendor_id foreign key (vendor_id) references delivery_vendors(id);
alter table deliveries add constraint fk_deliveries_dispatch_note_id foreign key (dispatch_note_id) references documents(id);
alter table delivery_checkpoints add constraint fk_delivery_checkpoints_delivery_id foreign key (delivery_id) references deliveries(id);
alter table pod_records add constraint fk_pod_records_delivery_id foreign key (delivery_id) references deliveries(id);
alter table pod_records add constraint fk_pod_records_proof_id foreign key (proof_id) references documents(id);
alter table pod_records add constraint fk_pod_records_closed_confirmed_by_id foreign key (closed_confirmed_by_id) references users(id);
alter table claims add constraint fk_claims_delivery_id foreign key (delivery_id) references deliveries(id);
alter table claims add constraint fk_claims_vendor_id foreign key (vendor_id) references delivery_vendors(id);
alter table container_returns add constraint fk_container_returns_container_id foreign key (container_id) references shipment_containers(id);
alter table sla_policies add constraint fk_sla_policies_client_id foreign key (client_id) references clients(id);
alter table sla_timers add constraint fk_sla_timers_shipment_id foreign key (shipment_id) references shipments(id);
alter table notifications add constraint fk_notifications_user_id foreign key (user_id) references users(id);
alter table notifications add constraint fk_notifications_shipment_id foreign key (shipment_id) references shipments(id);
alter table notification_prefs add constraint fk_notification_prefs_user_id foreign key (user_id) references users(id);
alter table chart_of_accounts add constraint fk_chart_of_accounts_parent_id foreign key (parent_id) references chart_of_accounts(id);
alter table cost_items add constraint fk_cost_items_shipment_id foreign key (shipment_id) references shipments(id);
alter table cost_items add constraint fk_cost_items_account_id foreign key (account_id) references chart_of_accounts(id);
alter table client_invoices add constraint fk_client_invoices_client_id foreign key (client_id) references clients(id);
alter table client_invoices add constraint fk_client_invoices_shipment_id foreign key (shipment_id) references shipments(id);
alter table client_invoices add constraint fk_client_invoices_approved_by_id foreign key (approved_by_id) references users(id);
alter table client_invoice_lines add constraint fk_client_invoice_lines_invoice_id foreign key (invoice_id) references client_invoices(id);
alter table client_invoice_lines add constraint fk_client_invoice_lines_cost_item_id foreign key (cost_item_id) references cost_items(id);
alter table vendor_bills add constraint fk_vendor_bills_shipment_id foreign key (shipment_id) references shipments(id);
alter table vendor_bills add constraint fk_vendor_bills_approved_by_id foreign key (approved_by_id) references users(id);
alter table vendor_payments add constraint fk_vendor_payments_bill_id foreign key (bill_id) references vendor_bills(id);
alter table vendor_payments add constraint fk_vendor_payments_account_id foreign key (account_id) references cash_bank_accounts(id);
alter table vendor_payments add constraint fk_vendor_payments_proof_id foreign key (proof_id) references documents(id);
alter table cash_bank_accounts add constraint fk_cash_bank_accounts_currency_id foreign key (currency_id) references currencies(id);
alter table cash_bank_transactions add constraint fk_cash_bank_transactions_account_id foreign key (account_id) references cash_bank_accounts(id);
alter table journal_entries add constraint fk_journal_entries_posted_by_id foreign key (posted_by_id) references users(id);
alter table journal_lines add constraint fk_journal_lines_entry_id foreign key (entry_id) references journal_entries(id);
alter table journal_lines add constraint fk_journal_lines_account_id foreign key (account_id) references chart_of_accounts(id);
alter table approvals add constraint fk_approvals_decided_by_id foreign key (decided_by_id) references users(id);
alter table employment_contracts add constraint fk_employment_contracts_employee_id foreign key (employee_id) references employees(id);
alter table employee_documents add constraint fk_employee_documents_employee_id foreign key (employee_id) references employees(id);
alter table employee_documents add constraint fk_employee_documents_file_id foreign key (file_id) references documents(id);
alter table asset_assignments add constraint fk_asset_assignments_asset_id foreign key (asset_id) references assets(id);
alter table asset_assignments add constraint fk_asset_assignments_employee_id foreign key (employee_id) references employees(id);
alter table asset_assignments add constraint fk_asset_assignments_handover_proof_id foreign key (handover_proof_id) references documents(id);
alter table biometric_consents add constraint fk_biometric_consents_employee_id foreign key (employee_id) references employees(id);
alter table attendance_logs add constraint fk_attendance_logs_employee_id foreign key (employee_id) references employees(id);
alter table attendance_logs add constraint fk_attendance_logs_location_id foreign key (location_id) references work_locations(id);
alter table leave_requests add constraint fk_leave_requests_employee_id foreign key (employee_id) references employees(id);
alter table leave_requests add constraint fk_leave_requests_approver_id foreign key (approver_id) references users(id);
alter table leave_balances add constraint fk_leave_balances_employee_id foreign key (employee_id) references employees(id);
alter table overtime_requests add constraint fk_overtime_requests_employee_id foreign key (employee_id) references employees(id);
alter table overtime_requests add constraint fk_overtime_requests_approver_id foreign key (approver_id) references users(id);
alter table cash_advances add constraint fk_cash_advances_employee_id foreign key (employee_id) references employees(id);
alter table payroll_runs add constraint fk_payroll_runs_approved_by_id foreign key (approved_by_id) references users(id);
alter table payslips add constraint fk_payslips_run_id foreign key (run_id) references payroll_runs(id);
alter table payslips add constraint fk_payslips_employee_id foreign key (employee_id) references employees(id);
alter table payslip_lines add constraint fk_payslip_lines_payslip_id foreign key (payslip_id) references payslips(id);
alter table crm_contacts add constraint fk_crm_contacts_client_id foreign key (client_id) references clients(id);
alter table crm_conversations add constraint fk_crm_conversations_contact_id foreign key (contact_id) references crm_contacts(id);
alter table crm_conversations add constraint fk_crm_conversations_channel_id foreign key (channel_id) references crm_channels(id);
alter table crm_conversations add constraint fk_crm_conversations_assignee_id foreign key (assignee_id) references users(id);
alter table crm_messages add constraint fk_crm_messages_conversation_id foreign key (conversation_id) references crm_conversations(id);
alter table crm_messages add constraint fk_crm_messages_template_id foreign key (template_id) references crm_templates(id);
alter table crm_deals add constraint fk_crm_deals_contact_id foreign key (contact_id) references crm_contacts(id);
alter table crm_deals add constraint fk_crm_deals_stage_id foreign key (stage_id) references crm_pipeline_stages(id);
alter table crm_deals add constraint fk_crm_deals_owner_id foreign key (owner_id) references users(id);
alter table client_portal_tokens add constraint fk_client_portal_tokens_client_id foreign key (client_id) references clients(id);
alter table client_portal_activity add constraint fk_client_portal_activity_token_id foreign key (token_id) references client_portal_tokens(id);
alter table client_portal_activity add constraint fk_client_portal_activity_shipment_id foreign key (shipment_id) references shipments(id);
alter table device_tokens add constraint fk_device_tokens_user_id foreign key (user_id) references users(id);
alter table mcp_credentials add constraint fk_mcp_credentials_user_id foreign key (user_id) references users(id);
alter table mcp_call_logs add constraint fk_mcp_call_logs_credential_id foreign key (credential_id) references mcp_credentials(id);
alter table ai_action_queue add constraint fk_ai_action_queue_shipment_id foreign key (shipment_id) references shipments(id);
alter table ai_action_queue add constraint fk_ai_action_queue_decided_by_id foreign key (decided_by_id) references users(id);
alter table import_jobs add constraint fk_import_jobs_file_id foreign key (file_id) references documents(id);
alter table import_jobs add constraint fk_import_jobs_created_by_id foreign key (created_by_id) references users(id);
alter table import_errors add constraint fk_import_errors_job_id foreign key (job_id) references import_jobs(id);

-- Indeks untuk seluruh foreign key
create index idx_role_permissions_role_id on role_permissions(role_id);
create index idx_role_permissions_perm_id on role_permissions(perm_id);
create index idx_users_employee_id on users(employee_id);
create index idx_user_roles_user_id on user_roles(user_id);
create index idx_user_roles_role_id on user_roles(role_id);
create index idx_client_assignments_user_id on client_assignments(user_id);
create index idx_client_assignments_client_id on client_assignments(client_id);
create index idx_audit_logs_actor_id on audit_logs(actor_id);
create index idx_integration_accounts_client_id on integration_accounts(client_id);
create index idx_exchange_rates_currency_id on exchange_rates(currency_id);
create index idx_ports_country_id on ports(country_id);
create index idx_customs_offices_port_id on customs_offices(port_id);
create index idx_client_contacts_client_id on client_contacts(client_id);
create index idx_client_warehouses_client_id on client_warehouses(client_id);
create index idx_client_poa_client_id on client_poa(client_id);
create index idx_client_poa_file_id on client_poa(file_id);
create index idx_shipments_client_id on shipments(client_id);
create index idx_shipments_port_id on shipments(port_id);
create index idx_shipments_incoterm_id on shipments(incoterm_id);
create index idx_shipments_created_by_id on shipments(created_by_id);
create index idx_shipment_pic_shipment_id on shipment_pic(shipment_id);
create index idx_shipment_pic_user_id on shipment_pic(user_id);
create index idx_shipment_pic_backup_id on shipment_pic(backup_id);
create index idx_shipment_events_shipment_id on shipment_events(shipment_id);
create index idx_shipment_events_actor_id on shipment_events(actor_id);
create index idx_documents_shipment_id on documents(shipment_id);
create index idx_shipment_required_documents_shipment_id on shipment_required_documents(shipment_id);
create index idx_document_validations_shipment_id on document_validations(shipment_id);
create index idx_document_validations_noted_by_id on document_validations(noted_by_id);
create index idx_shipment_items_shipment_id on shipment_items(shipment_id);
create index idx_shipment_items_origin_country_id on shipment_items(origin_country_id);
create index idx_shipment_items_hs_code_id on shipment_items(hs_code_id);
create index idx_shipment_items_hs_approved_by_id on shipment_items(hs_approved_by_id);
create index idx_item_hs_suggestions_item_id on item_hs_suggestions(item_id);
create index idx_item_hs_suggestions_hs_code_id on item_hs_suggestions(hs_code_id);
create index idx_hs_regulations_origin_country_id on hs_regulations(origin_country_id);
create index idx_client_product_master_client_id on client_product_master(client_id);
create index idx_client_product_master_hs_code_id on client_product_master(hs_code_id);
create index idx_shipment_declarations_shipment_id on shipment_declarations(shipment_id);
create index idx_shipment_declarations_doc_type_id on shipment_declarations(doc_type_id);
create index idx_shipment_declarations_approved_by_id on shipment_declarations(approved_by_id);
create index idx_customs_billings_shipment_id on customs_billings(shipment_id);
create index idx_customs_billings_proof_id on customs_billings(proof_id);
create index idx_outbound_jobs_shipment_id on outbound_jobs(shipment_id);
create index idx_api_sync_logs_shipment_id on api_sync_logs(shipment_id);
create index idx_api_sync_logs_job_id on api_sync_logs(job_id);
create index idx_shipment_voyages_shipment_id on shipment_voyages(shipment_id);
create index idx_shipment_voyages_carrier_id on shipment_voyages(carrier_id);
create index idx_shipment_voyages_pol_id on shipment_voyages(pol_id);
create index idx_shipment_voyages_pod_id on shipment_voyages(pod_id);
create index idx_shipment_containers_shipment_id on shipment_containers(shipment_id);
create index idx_shipment_containers_voyage_id on shipment_containers(voyage_id);
create index idx_shipment_containers_type_id on shipment_containers(type_id);
create index idx_tracking_events_voyage_id on tracking_events(voyage_id);
create index idx_carrier_emails_shipment_id on carrier_emails(shipment_id);
create index idx_carrier_emails_carrier_id on carrier_emails(carrier_id);
create index idx_carrier_invoices_shipment_id on carrier_invoices(shipment_id);
create index idx_carrier_invoices_carrier_id on carrier_invoices(carrier_id);
create index idx_carrier_invoices_currency_id on carrier_invoices(currency_id);
create index idx_carrier_invoices_approved_by_id on carrier_invoices(approved_by_id);
create index idx_do_validity_events_shipment_id on do_validity_events(shipment_id);
create index idx_shipment_holds_shipment_id on shipment_holds(shipment_id);
create index idx_shipment_holds_hold_type_id on shipment_holds(hold_type_id);
create index idx_shipment_holds_pic_id on shipment_holds(pic_id);
create index idx_shipment_hold_steps_hold_id on shipment_hold_steps(hold_id);
create index idx_shipment_hold_steps_pic_id on shipment_hold_steps(pic_id);
create index idx_shipment_hold_steps_evidence_id on shipment_hold_steps(evidence_id);
create index idx_field_inspections_shipment_id on field_inspections(shipment_id);
create index idx_field_inspections_hold_id on field_inspections(hold_id);
create index idx_field_inspections_inspector_id on field_inspections(inspector_id);
create index idx_field_inspections_report_id on field_inspections(report_id);
create index idx_vendor_rates_vendor_id on vendor_rates(vendor_id);
create index idx_vendor_quotes_shipment_id on vendor_quotes(shipment_id);
create index idx_vendor_quotes_vendor_id on vendor_quotes(vendor_id);
create index idx_vendor_quotes_approved_by_id on vendor_quotes(approved_by_id);
create index idx_deliveries_shipment_id on deliveries(shipment_id);
create index idx_deliveries_vendor_id on deliveries(vendor_id);
create index idx_deliveries_dispatch_note_id on deliveries(dispatch_note_id);
create index idx_delivery_checkpoints_delivery_id on delivery_checkpoints(delivery_id);
create index idx_pod_records_delivery_id on pod_records(delivery_id);
create index idx_pod_records_proof_id on pod_records(proof_id);
create index idx_pod_records_closed_confirmed_by_id on pod_records(closed_confirmed_by_id);
create index idx_claims_delivery_id on claims(delivery_id);
create index idx_claims_vendor_id on claims(vendor_id);
create index idx_container_returns_container_id on container_returns(container_id);
create index idx_sla_policies_client_id on sla_policies(client_id);
create index idx_sla_timers_shipment_id on sla_timers(shipment_id);
create index idx_notifications_user_id on notifications(user_id);
create index idx_notifications_shipment_id on notifications(shipment_id);
create index idx_notification_prefs_user_id on notification_prefs(user_id);
create index idx_chart_of_accounts_parent_id on chart_of_accounts(parent_id);
create index idx_cost_items_shipment_id on cost_items(shipment_id);
create index idx_cost_items_account_id on cost_items(account_id);
create index idx_client_invoices_client_id on client_invoices(client_id);
create index idx_client_invoices_shipment_id on client_invoices(shipment_id);
create index idx_client_invoices_approved_by_id on client_invoices(approved_by_id);
create index idx_client_invoice_lines_invoice_id on client_invoice_lines(invoice_id);
create index idx_client_invoice_lines_cost_item_id on client_invoice_lines(cost_item_id);
create index idx_vendor_bills_shipment_id on vendor_bills(shipment_id);
create index idx_vendor_bills_approved_by_id on vendor_bills(approved_by_id);
create index idx_vendor_payments_bill_id on vendor_payments(bill_id);
create index idx_vendor_payments_account_id on vendor_payments(account_id);
create index idx_vendor_payments_proof_id on vendor_payments(proof_id);
create index idx_cash_bank_accounts_currency_id on cash_bank_accounts(currency_id);
create index idx_cash_bank_transactions_account_id on cash_bank_transactions(account_id);
create index idx_journal_entries_posted_by_id on journal_entries(posted_by_id);
create index idx_journal_lines_entry_id on journal_lines(entry_id);
create index idx_journal_lines_account_id on journal_lines(account_id);
create index idx_approvals_decided_by_id on approvals(decided_by_id);
create index idx_employment_contracts_employee_id on employment_contracts(employee_id);
create index idx_employee_documents_employee_id on employee_documents(employee_id);
create index idx_employee_documents_file_id on employee_documents(file_id);
create index idx_asset_assignments_asset_id on asset_assignments(asset_id);
create index idx_asset_assignments_employee_id on asset_assignments(employee_id);
create index idx_asset_assignments_handover_proof_id on asset_assignments(handover_proof_id);
create index idx_biometric_consents_employee_id on biometric_consents(employee_id);
create index idx_attendance_logs_employee_id on attendance_logs(employee_id);
create index idx_attendance_logs_location_id on attendance_logs(location_id);
create index idx_leave_requests_employee_id on leave_requests(employee_id);
create index idx_leave_requests_approver_id on leave_requests(approver_id);
create index idx_leave_balances_employee_id on leave_balances(employee_id);
create index idx_overtime_requests_employee_id on overtime_requests(employee_id);
create index idx_overtime_requests_approver_id on overtime_requests(approver_id);
create index idx_cash_advances_employee_id on cash_advances(employee_id);
create index idx_payroll_runs_approved_by_id on payroll_runs(approved_by_id);
create index idx_payslips_run_id on payslips(run_id);
create index idx_payslips_employee_id on payslips(employee_id);
create index idx_payslip_lines_payslip_id on payslip_lines(payslip_id);
create index idx_crm_contacts_client_id on crm_contacts(client_id);
create index idx_crm_conversations_contact_id on crm_conversations(contact_id);
create index idx_crm_conversations_channel_id on crm_conversations(channel_id);
create index idx_crm_conversations_assignee_id on crm_conversations(assignee_id);
create index idx_crm_messages_conversation_id on crm_messages(conversation_id);
create index idx_crm_messages_template_id on crm_messages(template_id);
create index idx_crm_deals_contact_id on crm_deals(contact_id);
create index idx_crm_deals_stage_id on crm_deals(stage_id);
create index idx_crm_deals_owner_id on crm_deals(owner_id);
create index idx_client_portal_tokens_client_id on client_portal_tokens(client_id);
create index idx_client_portal_activity_token_id on client_portal_activity(token_id);
create index idx_client_portal_activity_shipment_id on client_portal_activity(shipment_id);
create index idx_device_tokens_user_id on device_tokens(user_id);
create index idx_mcp_credentials_user_id on mcp_credentials(user_id);
create index idx_mcp_call_logs_credential_id on mcp_call_logs(credential_id);
create index idx_ai_action_queue_shipment_id on ai_action_queue(shipment_id);
create index idx_ai_action_queue_decided_by_id on ai_action_queue(decided_by_id);
create index idx_import_jobs_file_id on import_jobs(file_id);
create index idx_import_jobs_created_by_id on import_jobs(created_by_id);
create index idx_import_errors_job_id on import_errors(job_id);

-- Tabel append-only: cabut UPDATE/DELETE agar tidak bisa diubah
do $$ declare t text; begin foreach t in array array['audit_logs','shipment_events','api_sync_logs','tracking_events','delivery_checkpoints','crm_messages','client_portal_activity','mcp_call_logs'] loop execute format('revoke update, delete on %I from public', t); end loop; end $$;
