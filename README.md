# Healthcare Clinic Landing Page

A WordPress website for presenting healthcare checkup packages and receiving visit inquiries. **Health Checkup Clinic** is the site name; the page layout and copy are editable in Elementor, while a small plugin handles package selection, pricing, and requests.

## Tech stack

| Technology | Role |
| --- | --- |
| WordPress and PHP | Content management, shortcode rendering, server-side form validation, and email |
| Elementor (free) | Editable landing page layout and content |
| Vanilla JavaScript and CSS | Checkup guide, live package pricing, HMO search, and responsive styling |
| MySQL | WordPress content and settings |
| Docker Compose and Mailpit | Local WordPress environment and captured test email |
| Node.js built-in test runner | Checks for the pricing logic and Elementor template |

Visitors can compare three packages, see how optional tests affect the total, use a non-diagnostic guide, search HMO names, and send a package request. The request form emails both the site administrator and the visitor; it does not confirm an appointment or take payment.

## Test locally with Docker Desktop

1. Start **Docker Desktop** and wait until its engine says it is running.
   Before starting, check for port conflicts with `docker ps --format "table {{.Names}}\t{{.Ports}}"` and `Get-NetTCPConnection -LocalPort 8080,8025 -State Listen` in PowerShell. This project uses only `127.0.0.1:8080` and `127.0.0.1:8025` on the host.
2. From this project folder, run `docker compose up -d`. The first run downloads WordPress, MySQL, and Mailpit images and can take several minutes.
3. Open <http://localhost:8080> and complete the WordPress setup. Use a local test administrator email such as `admin@health-checkup-clinic.test`.
4. In **Plugins → Installed Plugins**, activate **Healthcare Clinic Tools**. The plugin files are already mounted from this folder.
5. In **Plugins → Add New**, install and activate **Elementor Website Builder**. Then follow the Elementor import steps below.
6. Open <http://localhost:8025> to see captured emails. Submit one checkup request and confirm two messages appear: one for the site administrator and one auto reply for the visitor. Mailpit does not deliver them to real inboxes.

Quick manual checks: use age **29**, height **170 cm**, and weight **65 kg** for a Basic suggestion; change age to **40** for Standard, then check family history for Executive. In the builder, Basic + ECG should show **₱2,290**, Standard + ECG **₱3,790**, and Executive should show **₱4,990** with included tests disabled. Check that the request form follows each selection, HMO search filters the list, FAQ items open by keyboard, and the layout works at phone and desktop widths. Wait at least one minute between request submissions because the local form rate limit uses your network address.

For a PHP syntax check after the containers start, run `docker compose exec wordpress php -l wp-content/plugins/healthcare-clinic-tools/healthcare-clinic-tools.php`. Use `docker compose down` to stop the containers; the local site and database remain in Docker volumes.

## Install

1. Install WordPress and the free Elementor plugin on your site.
2. Copy `healthcare-clinic-tools/` to `wp-content/plugins/`, then activate **Healthcare Clinic Tools** in WordPress.
3. In **Elementor → Templates → Saved Templates**, import `elementor/healthcare-clinic-landing-page.json`.
4. Create a page, choose **Elementor Canvas** as its layout, open it in Elementor, and insert the imported template from **My Templates**. Publish it, then select it as the homepage in **Settings → Reading**.
5. Set the WordPress **Administration Email Address** to the inbox that should receive requests. Configure reliable outgoing mail on the site and submit a test request.

The page uses Elementor HTML widgets for its static sections and Elementor Shortcode widgets for functional tools. Static copy and section markup can be changed in those HTML widgets. The active package data lives in `hc_packages()` and `hc_addons()` in [healthcare-clinic-tools.php](healthcare-clinic-tools/healthcare-clinic-tools.php); keep clinical test descriptions and prices verified before publication.

## Configure clinic details before launch

Edit the contact Shortcode widget in Elementor. Example:

```text
[hc_contact phone="+63 2 8123 4567" whatsapp="639171234567" address="123 Example Street, Makati City, Philippines" hours="Monday to Friday, 8 AM to 5 PM"]
```

These values are **examples only**. A phone number enables click-to-call; a valid WhatsApp number enables the floating chat button; an address enables the embedded map. MedicalClinic JSON-LD is printed only when both phone and address are set. Replace the generic clinic name in the plugin and Elementor template when adapting this for a client.

The included package and add-on prices are starting values, not verified offers. Confirm each service, test, price, provider reference, and appointment process with the operating clinic before launch. The HMO list does **not** imply active coverage. Obtain a clinical review of checkup copy and preparation instructions, configure the real contact details and outgoing email, and update the privacy text for the clinic's mail and retention setup. Keep the calculator's non-diagnostic notice.

The preparation copy follows [MedlinePlus guidance](https://medlineplus.gov/lab-tests/fasting-for-a-blood-test/) to ask the clinic whether fasting is needed and to avoid stopping medicines without advice. A clinician still needs to approve the final instructions for the clinic's actual tests.

## How requests work

The calculator uses age, height, weight, activity, and family-history answers in the browser. Those answers are never submitted. The builder updates the estimated PHP price and fills the request form with the chosen package and add-ons. The server validates every choice and calculates the price again from the plugin's package data. On submission, WordPress emails the administrator and sends the visitor a preparation email. A honeypot and a one-minute rate limit reduce repeated submissions.

No appointment is confirmed automatically. Mail delivery depends on the WordPress host; test it before using the page. The form creates no request database in this plugin.

## Development checks

```text
node elementor/build-template.js
node --test --experimental-test-isolation=none healthcare-clinic-tools/assets/clinic.test.js elementor/build-template.test.js
```

The builder script regenerates the Elementor JSON after layout edits. The image is an original generated WebP asset. The local WordPress homepage is installed with Elementor Canvas at <http://localhost:8080/>. Chrome checks confirmed the calculator, package totals, HMO search, keyboard FAQ, form validation, and request emails in Mailpit. The layout has no horizontal overflow from 320 px through desktop. The mobile Lighthouse audit scored 100 for accessibility, best practices, and SEO. An unthrottled local Chrome trace measured 315 ms largest contentful paint and zero layout shift; public hosting may differ.

## Design direction

UI UX Pro Max's local medical clinic design search recommends an accessible, trust-led layout with calm teal, green, and white. This page applies that direction with large type, visible labels and focus states, 44 px navigation targets, restrained motion, and responsive sections.
