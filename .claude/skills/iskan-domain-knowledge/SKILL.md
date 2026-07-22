---
name: iskan-domain-knowledge
description: Domain knowledge for the Iskan (إسكان/تشغيل) housing-operations department this project serves — organizational sections (Abraj Kudanah, Sites A/B/C, Planning), camp/site geographical hierarchy and naming, and the Maximo work order lifecycle/status codes. Use whenever modeling, naming, or building features around departments, sites, camps, tents, squares, or work orders.
---

# Iskan Department Domain Knowledge

## When to use this skill

Use this skill whenever working on features that touch the department's organizational structure, site/camp/tent hierarchy, or work order workflows — including database modeling, Filament resources, naming, seeding, and validation logic.

## 1️⃣ Organizational Structure — 5 Sections (الهيكل التنظيمي والأقسام)

> **MOST IMPORTANT:** The distinction between **أبراج كدانة الوادي (Abraj Kudanah Al-Wadi)** and **Sites (أ), (ب), (ج)** — these are separate assets with separate contractors and asset managers. Do not conflate them.

| القسم / الأصول | المقاول (Contractor) | الاستشاري (Consultant) | مدير الأصل (Asset Manager) | كادر الإدارة / الإشراف |
| --- | --- | --- | --- | --- |
| **أبراج كدانة الوادي** | شركة الراجحي | إيهاف & عبد الله العيدروس | م. أحمد | **أخصائي الأصل:** سعيد الغامدي |
| **موقع (أ)** | عزام الشريف | إيهاف & عبد الله العيدروس | راشد الرفاعي | — |
| **موقع (ب)** | عزام الشريف | إيهاف & عبد الله العيدروس | عبد الله الأمير | — |
| **موقع (ج)** | الظاهري | إيهاف & عبد الله العيدروس | أحمد الصبحي | — |
| **التخطيط** | — | — | فهد مقلان _(مدير)_ | **المدير التنفيذي (Director):** عبد الله باحويرث |

## 2️⃣ Geographical Hierarchy & Tent Naming (الهيكلية الجغرافية وتسميات الخيام)

Naming and hierarchy vary by camp type (regular vs developed).

### ⛺ Site hierarchy (smallest to largest)

- 🏠 **خيمة (Tent)**
  - 🏕️ **مخيم (Camp)** — a group of tents
    - 🧱 **مربع (Square)** — a group of camps

> [!INFO] الشاخص ورقم البوابة (Signpost & Gate Number)
> - **الشاخص (signpost)** is the same as **رقم المخيم (camp number)**.
> - Written as a fraction (numerator over denominator): `رقم البوابة / الشارع` (gate number / street) — e.g. `8/194`.

### 🔄 Regular vs Developed Camps (المخيمات العادية والمطورة)

- **Developed camps (المخيمات المطورة):**
  - The terms **مخيم = كلستر (cluster) = مربع** all mean the same level.
  - **Developed square naming pattern:** usually letters and numbers, e.g. `A`, `B`, `C`, `D`, `105`, `106`, `118أ`.
- **Regular (non-developed) camps (المخيمات العادية):**
  - **المربع (square):** means a group of camps together.
  - **Regular square naming pattern:** starts with `HC` or similar forms.

## 3️⃣ Maximo Work Order Lifecycle (دورة حياة أمر العمل في نظام ماكسيمو)

### 📌 Status Codes (رموز الحالات)

- `WSCH` **(Waiting for Schedule):** بانتظار جدولة أمر العمل.
- `IPRG` **(In Progress):** قيد التنفيذ حالياً عند المقاول.
- `CTCOM` **(Completed/Pending Review):** أنجزها المقاول وهي بانتظار مراجعة الاستشاري.
- `مدير الأصل`: مرحلة مراجعة وتدقيق مدير الأصل.
- `CON-APP` **(Approved):** الاعتماد النهائي لأمر العمل.

### 🔄 Work Order Workflow (مسار إجراء أمر العمل)

1. **Create work order** ⬅️ status `WSCH` (waiting for schedule).
2. **Start work** ⬅️ status `IPRG` (in progress at the contractor).
3. **Finish execution** ⬅️ status `CTCOM` (at the consultant for review):
   - _Consultant action:_ either return it to the contractor (`Rework`) ⬅️ back to `IPRG`,
   - _or_ forward it to the **Asset Manager (مدير الأصل)**.
4. **Asset Manager review:**
   - _Asset Manager action:_ either return it to the contractor (`Rework`) ⬅️ back to `IPRG`,
   - _or_ final approval ⬅️ becomes `CON-APP`.

> [!NOTE] Rework authority
> - **The consultant (الاستشاري)** can send a work order back to the contractor for correction (`Rework` → `IPRG`).
> - **The asset manager (مدير الأصل)** can either send it back to the contractor (`Rework` → `IPRG`) or approve it finally (`CON-APP`).

#إسكان #تشغيل #ماكسيمو #Maximo #كدانة
