# 🌆 Mumbai Glam Studio 
### *Where Mumbai Gets Its Glow On* 

Welcome to **Mumbai Glam Studio**, a luxury startup-style beauty salon marketplace designed specifically for Mumbaikars. Built in under 48 hours for the **SuperXgen AI Startup Buildathon 2026 — Beauty Salon Marketplace Challenge**, this platform bridges the gap between premium local beauty studios and busy customers looking for seamless, instant appointment management.

🔗 **[Live Website Link](https://mumbai-glam-studio.infinityfree.me/)**  
🎬 **[2-3 Minute Demo Video Link](YOUR_YOUTUBE_OR_LOOM_LINK_HERE)**

---

## 🚀 The Core Value Proposition
Mumbai's fast pace and unpredictable monsoons make scheduling beauty treatments a hassle. Mumbai Glam Studio solves this through:
* **Locality-Based Discovery:** Seamlessly browse handpicked premium salons across specific Mumbai neighborhoods (Andheri, Bandra, Dadar, Powai, etc.).
* **☔ Smart Monsoon-Proof (Rain-Safe) Filter:** A killer contextual feature allowing users to filter exclusively for salons featuring verified, fully covered indoor setups with zero weather-disruption risks.
* **Dual-Dashboard Portal:** Fully decoupled, functional interfaces for both sides of the marketplace—enabling consumers to track their booking histories while giving service providers complete control over their operations.

---

## 🛠️ Tech Stack & AI Rapid Workflow
This project was aggressively prototyped and engineered using cutting-edge AI assistance to maximize execution velocity without compromising code quality.

### Codebase & Systems
* **Backend Engine:** Vanilla PHP (Object-Oriented, Prepared PDO / MySQLi statements for secure database transactions)[cite: 11, 12].
* **Database Management:** MySQL (Relational tables handling dynamic stat aggregations, cross-referenced localities, booking states, and securely hashed passwords).
* **Frontend Design:** Semantic HTML5, CSS3 Custom Properties (Design Tokens System), Native Vanilla JavaScript (Interactions & Event Handlers)[cite: 11, 12].

### The AI Tech Stack
* **DeepSeek (Core Architect):** Acted as the primary pair-programmer to write highly modular PHP routing, handle complex multi-table SQL queries, design secure authentication pipelines, and structure granular input form validations.
* **Figma AI:** Leveraged for immediate structural wireframing and micro-interaction UX planning.

---

## ✨ Features Implemented

### 1. Unified Landing Page & Discovery Engine
* **Dynamic Analytics Engine:** Runs real-time aggregation queries calculating verified salon counts, active neighborhoods, and community-wide average ratings[cite: 12].
* **Premium UX Layering:** Fully customized interactive CSS/JS scripts providing a responsive, luxury perspective tilt on salon cards based on active mouse coordinates.

### 2. Customer Dashboard (`customer_dashboard.php`)
* **Real-time Status Categorization:** Auto-separates customer reservations into **Upcoming Bookings** and **Past Bookings** by comparing reservation dates against system timestamps[cite: 11].
* **Visual Status Identifiers:** Native UI components mapping out real-time service tracking indicators (`Pending`, `Confirmed`, or `Completed`)[cite: 11].
* **Instant KPIs:** High-level metrics blocks counting upcoming commitments vs. past completed luxury treatments at a glance[cite: 11].

### 3. Salon & Admin Command Center (`dashboard.php`)
* **Live Appointment Manager:** Allows business operators to interactively switch booking nodes from `Pending` ➡️ `Confirmed` ➡️ `Completed` directly through server-validated forms[cite: 12].
* **Granular Data Sheets:** Surfaces customer identification records, service classification schemas, explicit booking windows, and formatted contact nodes (`customer_phone`)[cite: 12].
* **Administrative Governance Pipeline:** A conditional admin privilege overlay that surfaces an access-controlled verification console[cite: 12]. Admins can audit unregistered storefront platforms and issue a global `✨ Verified` trust-badge or remove compliance-failing entities[cite: 12].

---

## 📂 Repository Architecture
```text
├── assets/                  # Images, luxury favicons, and uploaded studio previews
├── includes/                # Global re-usable components (nav.php, footer.php, header.php)
├── config.php               # Core Database configuration wrapper 
├── index.php                # High-conversion Marketplace landing page with dynamic metrics
├── login.php                # Unified Dual-portal gateway (Customer/Salon/Admin)
├── logout.php               # Secure session destruction module
├── register.php             # Context-aware user/business onboarding layout
├── salons.php               # Advanced marketplace filtering directory (Locality + Rain-Safe)
├── schema.sql               # Production database schema and sample seed entries
├── customer_dashboard.php   # Customer app-like tracking interface & booking stats
├── dashboard.php            # Salon operator scheduling deck & Admin verification panel
├── style.css                # Luxury Design System tokens (Light/Dark mode palettes)
└── script.js               # Frontend micro-interactions & data formatting handlers
