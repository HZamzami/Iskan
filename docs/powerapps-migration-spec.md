# Iskan Archive — Power Apps Migration Specification

A complete, Plan-designer-ready specification of the Iskan (إسكان الحجاج بمشعر منى) document-archive system, extracted from the production Laravel/Filament codebase. Paste the **Plan Description** section into the Power Apps Plan designer (make.powerapps.com → Plan), then use the detailed sections below to verify and refine each table, choice set, and security role that Copilot generates.

---

## 1. Plan Description (paste this into Plan designer)

> Build a document archive and records management system for the Pilgrims Housing Operations department (إدارة إسكان الحجاج). The department manages four physical assets: Site A (موقع أ), Site B (موقع ب), Site C (موقع ج), and Abraj Kudanah Al-Wadi (أبراج كدانة الوادي).
>
> The system archives seven types of records, each with an uploaded PDF/file attachment, an auto-generated reference number, a document date, and optional notes:
>
> 1. **Correspondences (المعاملات الإدارية)** — incoming/outgoing letters with subject, sender, recipient, a related external Entity (جهة), and a workflow status (New → In Progress → Completed → Archived).
> 2. **Contract Documents (المستندات التعاقدية)** — consultant, operation, and internal-project contracts with contract number, contracting party, start/end dates, and an optional site.
> 3. **Minutes (المحاضر)** — meeting and handover minutes with a type, involved parties, and an optional site.
> 4. **Financial Flows (التدفقات المالية)** — monthly payment certificates with a type, period month, amount in SAR, and an optional site.
> 5. **Contractual Requirements (المتطلبات التعاقدية)** — monthly counts, operation documents, and management plans, grouped by category, with a period and an optional site.
> 6. **Periodic Reports (التقارير الدورية)** — monthly/final/weekly reports with a type, period, and an optional site.
> 7. **Geo Documents (الخرائط والرسومات الجيومكانية)** — GIS files, KML/KMZ, and as-built drawings with a drawing number and an optional site.
>
> Plus a lookup table of **Entities (الجهات)** — external organizations referenced by correspondences.
>
> **Users and security:** Admins have full access to everything. Regular users are granted, per module, one of three levels: Read, Write (read + create), or Edit (read + create + update + delete). Independently, each user is granted access to specific sites; they can only see records belonging to their allowed sites, plus "general" records that have no site. All create/update/delete operations must be logged in an audit trail.
>
> **Apps needed:** one model-driven app for the whole archive with a dashboard showing record counts per module, correspondence status breakdown, correspondence volume trend over time, financial flow totals by month, contracts expiring soon, and latest documents. The UI language is Arabic (right-to-left).

---

## 2. Sites (المواقع) — global choice set `Site`

Used by all site-scoped tables. Do **not** model as a table unless you later need per-site attributes; a global choice is enough today.

| Value | Arabic label | Contractor | Asset manager |
|---|---|---|---|
| `site_a` | موقع (أ) | عزام الشريف | راشد الرفاعي |
| `site_b` | موقع (ب) | عزام الشريف | عبد الله الأمير |
| `site_c` | موقع (ج) | الظاهري | أحمد الصبحي |
| `abraj_kudanah` | أبراج كدانة الوادي | شركة الراجحي | م. أحمد |

Business rule used in forms: some record types apply only to the three camp sites (A, B, C) and never to Abraj Kudanah — noted per type below as "camp sites only".

---

## 3. Tables (Dataverse)

Every archive table below shares these common columns:

| Column | Dataverse type | Required | Notes |
|---|---|---|---|
| `reference_number` | Autonumber (or text + Power Automate) | Yes, unique | Pattern: `{prefix}-{year}-{sequence:0000}`, sequence resets yearly. Prefixes listed per table. Dataverse autonumber cannot reset yearly — implement with a Power Automate flow on create, or accept a non-resetting sequence. |
| `title` / `subject` | Single line of text | Yes | Primary name column |
| `site` | Choice (`Site`) | No | Empty = "general" record visible to all users |
| `document_date` | Date only | Yes | |
| `file` | File column | Yes | Replaces Laravel `file_path`; max size per org settings |
| `notes` | Multiple lines of text | No | |
| created/modified on/by | built-in | — | Replaces Laravel timestamps |

### 3.1 Entity — جهة (lookup table)

| Column | Type | Required |
|---|---|---|
| `name` (primary) | Text, unique | Yes |

Relationship: one Entity → many Correspondences (restrict delete: an Entity with correspondences cannot be deleted).

### 3.2 Correspondence — معاملة إدارية

No `site` column — correspondences are the only module that is **not** site-scoped.

| Column | Type | Required | Notes |
|---|---|---|---|
| `reference_number` | Text, unique | Yes | Prefix `و` (incoming) / `ص` (outgoing), e.g. `و-2026-0007`; sequence counted **per direction** per year |
| `subject` (primary) | Text | Yes | |
| `direction` | Choice | Yes | `incoming` = وارد, `outgoing` = صادر |
| `status` | Choice | Yes, default `new` | `new` = جديدة, `in_progress` = قيد المعالجة, `completed` = منجزة, `archived` = مؤرشفة |
| `sender` | Text | Yes | |
| `recipient` | Text | Yes | |
| `entity` | Lookup → Entity | Yes | Restrict delete |
| `document_date`, `file`, `notes` | common | | |

### 3.3 Contract Document — مستند تعاقدي (ref prefix: `عقد`)

| Column | Type | Required | Notes |
|---|---|---|---|
| `type` | Choice | Yes | see below |
| `site` | Choice (Site) | No | Only meaningful for type `operation_contract`; other types are always general (no site) |
| `title` (primary) | Text | Yes | |
| `contract_number` | Text | No | |
| `contracting_party` | Text | No | |
| `start_date`, `end_date` | Date only | No | Used by "expiring contracts" dashboard widget |
| common columns | | | |

Choice `ContractDocumentType`: `consultant_contract` = عقود الصيانة والتشغيل الإستشاري · `operation_contract` = عقود الصيانة والتشغيل · `internal_project_contract` = عقود المشاريع الداخلية

### 3.4 Minute — محضر (ref prefix: `محضر`)

| Column | Type | Required | Notes |
|---|---|---|---|
| `type` | Choice | Yes | see below |
| `site` | Choice (Site) | No | site applies only to types marked below |
| `title` (primary) | Text | Yes | |
| `parties` | Text | No | Involved parties, free text |
| common columns | | | |

Choice `MinuteType` (site applicability in brackets):
`weekly_meeting` = محاضر الاجتماعات الأسبوعية [all sites] · `project_handover` = محاضر تسليم واستلام المشاريع [general] · `service_provider` = محاضر شركات تقديم الخدمة [general] · `service_provider_re_receipt` = محاضر إعادة استلام من شركات تقديم الخدمة [general] · `damages_extensions` = محاضر التلفيات والتمديدات [general] · `asset_removal` = محاضر إزالة الأصول من المواقع [general] · `asset_tagging` = محاضر تسليم علامات ترميز الأصول [camp sites only] · `spare_parts_handover` = محاضر تسليم واستلام قطع الغيار [camp sites only] · `ac_sterilization_receipt` = محضر استلام أقراص تعقيم المكيفات [general]

### 3.5 Financial Flow — تدفق مالي (ref prefix: `تدفق`)

| Column | Type | Required | Notes |
|---|---|---|---|
| `type` | Choice | Yes | see below |
| `site` | Choice (Site) | No | Only for type `operation`; other types are general |
| `title` (primary) | Text | Yes | |
| `period_month` | Date only | Yes | First day of the month it covers |
| `amount` | Currency (SAR), 2 decimals | No | |
| common columns | | | |

Choice `FinancialFlowType`: `consultant` = التدفقات المالية الخاصة بعقد الإستشاري · `operation` = التدفقات المالية الخاصة بعقد الصيانة والتشغيل · `internal_projects` = التدفقات المالية الخاصة بعقود المشاريع الداخلية

### 3.6 Contractual Requirement — متطلب تعاقدي (ref prefix: `متطلب`)

| Column | Type | Required | Notes |
|---|---|---|---|
| `type` | Choice | Yes | 17 values in 3 groups, see below |
| `site` | Choice (Site) | No | Management-plans group: camp sites only; other groups: any site |
| `title` (primary) | Text | Yes | |
| `period` | Date only | No | |
| common columns | | | |

Choice `ContractualRequirementType`, grouped (model the group as a calculated/derived display or a second choice column kept in sync):

**Group الحصور الشهرية (monthly_counts):** `labor_count` = حصر العمالة الشهري · `equipment_count` = حصر المعدات الشهري · `spare_parts_count` = حصر قطع الغيار الشهري · `tools_count` = حصر الأدوات الشهري

**Group وثائق التشغيل (operation_docs):** `org_structure` = الهيكل التنظيمي للاستشاري والمقاول · `sop` = إجراءات التشغيل الموحد (SOP) · `completion_certificates` = شهادات إنجاز الأعمال · `master_plan` = الجداول الزمنية (Master Plan) · `job_plan` = بنود وخطوات عمل الصيانات (Job Plan)

**Group الخطط الإدارية (management_plans) [camp sites only]:** `quality_plan` = خطة إدارة الجودة · `risk_plan` = خطة إدارة المخاطر · `hse_plan` = خطة إدارة السلامة والصحة المهنية · `stakeholder_plan` = خطة إدارة أصحاب المصلحة · `cash_flow_plan` = خطة التدفقات النقدية · `elemental_cost_plan` = خطط التكاليف المرحلية والنوعية · `logistics_plan` = خطة إدارة اللوجستيات · `interface_plan` = خطة إدارة التداخلات

### 3.7 Periodic Report — تقرير دوري (ref prefix: `تقرير`)

| Column | Type | Required |
|---|---|---|
| `type` | Choice | Yes |
| `site` | Choice (Site) | No |
| `title` (primary) | Text | Yes |
| `period` | Date only | Yes |
| common columns | | |

Choice `PeriodicReportType`: `monthly_report` = التقرير الشهري لمنظومة إسكان الحجاج بمشعر منى · `final_report` = التقرير الختامي لإدارة إسكان الحجاج بمشعر منى · `weekly_progress` = تقارير إنجاز الأعمال الأسبوعية · `weekly_inventory_coding` = تقارير الحصر والترميز الأسبوعية · `guidelines` = الأدلة الإسترشادية والإجرائية

### 3.8 Geo Document — رسم جيومكاني (ref prefix: `خريطة`)

| Column | Type | Required |
|---|---|---|
| `type` | Choice | Yes |
| `site` | Choice (Site) | No |
| `title` (primary) | Text | Yes |
| `drawing_number` | Text | No |
| common columns | | |

Choice `GeoDocumentType`: `gis` = GIS · `kml_kmz` = KML & KMZ · `as_built_drawing` = المخططات كما نُفذت (As Built Drawing)

---

## 4. Security model

The Laravel app uses two **independent** permission axes; both must be reproduced.

### 4.1 Axis 1 — module × access level

Per module, a user holds at most one level (higher levels include lower ones):

| Level | Arabic | Grants |
|---|---|---|
| Read (قراءة) | | view records |
| Write (إضافة) | | view + create |
| Edit (تعديل) | | view + create + update + delete |

Dataverse mapping: create three security roles **per module** (e.g. `Correspondences – Read`, `Correspondences – Write`, `Correspondences – Edit`) with organization-level Read and the corresponding Create/Write/Delete privileges on that one table. Users receive one role per module they can access. The **admin** role has full privileges on all tables plus user administration.

Modules: Correspondences (المعاملات الإدارية), Contract Documents (المستندات التعاقدية), Minutes (المحاضر), Financial Flows (التدفقات المالية), Contractual Requirements (المتطلبات التعاقدية), Periodic Reports (التقارير الدورية), Geo Documents (الخرائط والرسومات الجيومكانية).

### 4.2 Axis 2 — site access (row-level)

- Admins see all records.
- A non-admin user has a set of allowed sites (possibly empty).
- On every site-scoped table the user sees: records whose `site` is empty (general records) **plus** records whose `site` is in their allowed set.
- An empty allowed set means general records only.
- Correspondences are exempt (not site-scoped).

Dataverse has no native "filter rows by a choice column per user" — pick one:

1. **Business units + site as lookup** (most robust): convert `site` from a choice to a lookup on a small Site table, assign records to per-site business units, put users in the business units they may access via team membership. General records live in the root BU with org-wide read.
2. **Per-site Dataverse teams + record sharing via Power Automate** (simpler to set up): a flow on create/update shares the record with the matching site team; general records get org-wide read.
3. **View-level filtering only** (weakest — not real security): filter views by the user's site membership. Not recommended as the only mechanism.

Recommendation: option 1 if site access must be a hard security boundary (it is in the current app — the query is filtered server-side), option 2 if pragmatism wins.

### 4.3 Users

Standard columns (name, email, password) map to Entra ID accounts — authentication is replaced entirely by Microsoft Entra. The seeded bootstrap admin (`admin@iskan.test`) is replaced by assigning the System Administrator (or custom admin) role to a real account.

---

## 5. Business rules & automation (Power Automate)

1. **Reference number generation** — on create of each archive record, if reference number is blank, set `{prefix}-{year}-{sequence}` where sequence = count of records of that table (for correspondences: of that direction) created in the current calendar year + 1. Prefixes: correspondences `و`/`ص`, contracts `عقد`, minutes `محضر`, financial flows `تدفق`, requirements `متطلب`, reports `تقرير`, geo `خريطة`.
2. **Site applicability** — form logic (business rules or JS): show/require/clear the `site` field per type as documented in §3 ("general", "all sites", "camp sites only" — camp sites exclude Abraj Kudanah).
3. **Audit trail** — enable Dataverse auditing on all archive tables and Users (replaces spatie/laravel-activitylog; logs old/new values of changed fields on create/update/delete).
4. **Entity delete protection** — configure the Entity→Correspondence relationship as restrict-delete.

---

## 6. Dashboard (model-driven dashboard or embedded Power BI)

Recreate these widgets from the current Filament dashboard:

| Widget | Content |
|---|---|
| Archive overview stats | Record count per module |
| Site overview | Per-site record counts / contractor & asset manager info (from §2) |
| Correspondence stats | Counts by status (new / in progress / completed / archived) |
| Correspondence trend chart | Correspondence volume over time, split by direction |
| Financial flows chart | Amounts by period month, split by type |
| Expiring contracts | Contract documents with `end_date` approaching |
| Latest documents | Most recent records across all modules |
| Recent activity | Latest audit-log entries |
| Quick actions | "New record" shortcuts per module the user can write to |

Power BI embedded is the better fit for the trend/financial charts; native model-driven charts cover the counts.

---

## 7. Localization

The entire UI is Arabic, right-to-left. Set the environment/user language to Arabic (LCID 1025) and enter the Arabic display names from this document as the table/column/choice labels; keep the English schema names as listed for maintainability.

---

## 8. Data migration

1. Export each table to CSV from the Laravel database (MySQL) — column names in this spec match the source columns 1:1.
2. Import into Dataverse via **Dataflows** (Power Query, can connect straight to MySQL) or CSV import. Import Entities before Correspondences (lookup dependency).
3. Files: the `file_path` column points to files on the Laravel server's storage disk. Download them, then upload into the Dataverse file column per record (Power Automate flow reading from a temporary SharePoint library is the usual path).
4. Preserve `reference_number` values as-is on import; only new records use the auto-numbering flow.
5. Dates are stored as `Y-m-d`; amounts as decimal SAR.

---

## 9. Known gaps / decisions to make in Power Apps

- **Yearly-resetting reference sequences** need a custom flow (Dataverse autonumber can't reset). Decide whether the reset actually matters going forward.
- **Row-level site security** requires business units or sharing flows (§4.2) — the single most complex part of the migration; decide the mechanism before generating tables, because option 1 changes `site` from a choice to a lookup.
- **Per-direction correspondence sequences** (`و-2026-0001` and `ص-2026-0001` counted separately) need the flow to filter by direction.
- **Licensing:** all users need Power Apps premium licenses (Dataverse).

---

## Appendix A — Global choice sets (ready to enter)

Create these as **global choices** inside the solution before building tables. Schema names use the `iskan_` publisher prefix automatically; keep the English names below, enter the Arabic as option labels, in the listed order. Auto-generated integer values are fine — never change them once data exists.

### A.1 `Site`

1. موقع (أ)
2. موقع (ب)
3. موقع (ج)
4. أبراج كدانة الوادي

### A.2 `CorrespondenceDirection`

1. وارد
2. صادر

### A.3 `CorrespondenceStatus`

1. جديدة
2. قيد المعالجة
3. منجزة
4. مؤرشفة

### A.4 `ContractDocumentType`

1. عقود الصيانة والتشغيل الإستشاري
2. عقود الصيانة والتشغيل
3. عقود المشاريع الداخلية

### A.5 `MinuteType`

1. محاضر الاجتماعات الأسبوعية
2. محاضر تسليم واستلام المشاريع
3. محاضر شركات تقديم الخدمة
4. محاضر إعادة استلام من شركات تقديم الخدمة
5. محاضر التلفيات والتمديدات
6. محاضر إزالة الأصول من المواقع من قبل شركات تقديم الخدمة
7. محاضر تسليم علامات ترميز الأصول
8. محاضر تسليم واستلام قطع الغيار
9. محضر استلام أقراص تعقيم المكيفات

### A.6 `FinancialFlowType`

1. التدفقات المالية الخاصة بعقد الإستشاري
2. التدفقات المالية الخاصة بعقد الصيانة والتشغيل
3. التدفقات المالية الخاصة بعقود المشاريع الداخلية

### A.7 `ContractualRequirementType`

Group الحصور الشهرية:

1. حصر العمالة الشهري
2. حصر المعدات الشهري
3. حصر قطع الغيار الشهري
4. حصر الأدوات الشهري

Group وثائق التشغيل:

5. الهيكل التنظيمي للاستشاري والمقاول
6. إجراءات التشغيل الموحد (SOP)
7. شهادات إنجاز الأعمال
8. الجداول الزمنية (Master Plan)
9. بنود وخطوات عمل الصيانات (Job Plan)

Group الخطط الإدارية:

10. خطة إدارة الجودة
11. خطة إدارة المخاطر
12. خطة إدارة السلامة والصحة المهنية
13. خطة إدارة أصحاب المصلحة (المعنيين)
14. خطة التدفقات النقدية
15. خطط التكاليف المرحلية والنوعية (Elemental Cost Plan)
16. خطة إدارة اللوجستيات
17. خطة إدارة التداخلات (Interface Management Plan)

### A.8 `PeriodicReportType`

1. التقرير الشهري لمنظومة إسكان الحجاج بمشعر منى
2. التقرير الختامي لإدارة إسكان الحجاج بمشعر منى
3. تقارير إنجاز الأعمال الأسبوعية
4. تقارير الحصر والترميز الأسبوعية
5. الأدلة الإسترشادية والإجرائية

### A.9 `GeoDocumentType`

1. GIS
2. KML & KMZ
3. المخططات كما نُفذت (As Built Drawing)

### A.10 `RequirementGroup` (optional)

Only needed if you want a filterable group column on Contractual Requirements.

1. الحصور الشهرية
2. وثائق التشغيل
3. الخطط الإدارية

---

## Appendix B — Tables (ready to enter)

Create in this order (Entity first — Correspondence's lookup needs it). For every table: **New → Table → Table (advanced)** inside the solution; set the primary column as noted; after adding columns, create the unique **Key** on Reference Number; enable **Audit changes to its data** in table properties.

Required = Business required. Choice columns must **sync with the global choice set** named in brackets (from Appendix A), not create a local one.

### B.1 Entity — جهة

Primary column: `Name` (الاسم)

No other columns.

### B.2 Correspondence — معاملة إدارية

Primary column: `Subject` (الموضوع)

1. Reference Number (الرقم المرجعي) — Single line of text — Required — unique Key
2. Direction (الاتجاه) — Choice [`CorrespondenceDirection`] — Required
3. Status (الحالة) — Choice [`CorrespondenceStatus`] — Required — default: جديدة
4. Sender (المرسل) — Single line of text — Required
5. Recipient (المستلم) — Single line of text — Required
6. Entity (الجهة) — Lookup → Entity — Required — relationship delete behavior: **Restrict**
7. Document Date (تاريخ المعاملة) — Date only — Required
8. File (الملف) — File
9. Notes (ملاحظات) — Multiple lines of text

### B.3 Contract Document — مستند تعاقدي

Primary column: `Title` (العنوان)

1. Reference Number (الرقم المرجعي) — Single line of text — Required — unique Key
2. Type (النوع) — Choice [`ContractDocumentType`] — Required
3. Site (الموقع) — Choice [`Site`] — Optional
4. Contract Number (رقم العقد) — Single line of text
5. Contracting Party (الطرف المتعاقد) — Single line of text
6. Start Date (تاريخ البداية) — Date only
7. End Date (تاريخ النهاية) — Date only
8. Document Date (تاريخ المستند) — Date only — Required
9. File (الملف) — File
10. Notes (ملاحظات) — Multiple lines of text

### B.4 Minute — محضر

Primary column: `Title` (العنوان)

1. Reference Number (الرقم المرجعي) — Single line of text — Required — unique Key
2. Type (النوع) — Choice [`MinuteType`] — Required
3. Site (الموقع) — Choice [`Site`] — Optional
4. Parties (الأطراف) — Single line of text
5. Document Date (تاريخ المحضر) — Date only — Required
6. File (الملف) — File
7. Notes (ملاحظات) — Multiple lines of text

### B.5 Financial Flow — تدفق مالي

Primary column: `Title` (العنوان)

1. Reference Number (الرقم المرجعي) — Single line of text — Required — unique Key
2. Type (النوع) — Choice [`FinancialFlowType`] — Required
3. Site (الموقع) — Choice [`Site`] — Optional
4. Period Month (شهر الفترة) — Date only — Required
5. Amount (المبلغ) — Currency — 2 decimal places (Dataverse auto-adds an exchange-rate column; ignore it)
6. Document Date (تاريخ المستند) — Date only — Required
7. File (الملف) — File
8. Notes (ملاحظات) — Multiple lines of text

### B.6 Contractual Requirement — متطلب تعاقدي

Primary column: `Title` (العنوان)

1. Reference Number (الرقم المرجعي) — Single line of text — Required — unique Key
2. Type (النوع) — Choice [`ContractualRequirementType`] — Required
3. Group (المجموعة) — Choice [`RequirementGroup`] — Optional (only if you created A.10)
4. Site (الموقع) — Choice [`Site`] — Optional
5. Period (الفترة) — Date only
6. Document Date (تاريخ المستند) — Date only — Required
7. File (الملف) — File
8. Notes (ملاحظات) — Multiple lines of text

### B.7 Periodic Report — تقرير دوري

Primary column: `Title` (العنوان)

1. Reference Number (الرقم المرجعي) — Single line of text — Required — unique Key
2. Type (النوع) — Choice [`PeriodicReportType`] — Required
3. Site (الموقع) — Choice [`Site`] — Optional
4. Period (الفترة) — Date only — Required
5. Document Date (تاريخ المستند) — Date only — Required
6. File (الملف) — File
7. Notes (ملاحظات) — Multiple lines of text

### B.8 Geo Document — رسم جيومكاني

Primary column: `Title` (العنوان)

1. Reference Number (الرقم المرجعي) — Single line of text — Required — unique Key
2. Type (النوع) — Choice [`GeoDocumentType`] — Required
3. Site (الموقع) — Choice [`Site`] — Optional
4. Drawing Number (رقم الرسم) — Single line of text
5. Document Date (تاريخ المستند) — Date only — Required
6. File (الملف) — File
7. Notes (ملاحظات) — Multiple lines of text
