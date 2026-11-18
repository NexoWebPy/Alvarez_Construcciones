# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Primary users are prospective customers in Paraguay researching A-Frame homes, cabins, pools, or a custom construction project. Existing customers use a private portal to follow their project and access uploaded documents. Administrators update project progress and manage client files.

## Product Purpose

Present AC Alvarez Construcciones' models and services clearly, help prospective customers start a conversation through WhatsApp, and give existing customers access to project progress and documents.

## Positioning

AC Alvarez Construcciones designs and builds A-Frame homes, cabins, pools, and custom construction projects in Paraguay. No distinct competitive differentiator has been confirmed; do not invent one.

## Operating Context

Prospective customers browse models and pricing before requesting information. Current customers sign in to view work progress, spending updates, photos, and receipts. Administrators update project metrics and upload or edit files.

## Capabilities and Constraints

- Public pages include the main site, project catalog, customer login, privacy notice, and terms.
- The private customer portal shows project progress, spending, photos, and documents.
- The administrator portal manages project progress, spending, and uploaded files.
- WhatsApp is the primary inquiry channel.
- Existing prices, phone/WhatsApp details, services, and photography were confirmed current by the user.
- Preserve authentication, role restrictions, CSRF validation, file validation, and client-specific document access.

## Brand Commitments

- Use the existing AC Alvarez Construcciones name, logo, and real project photography.
- Keep copy in Spanish for Paraguay and preserve the existing local voseo.
- Do not claim a competitive differentiator that the user has not supplied.

## Evidence on Hand

- The project catalog contains three A-Frame models, current prices, dimensions, room counts, descriptions, and specifications in `proyectos.php`.
- Existing project photography and construction videos are available in the workspace.
- Customer and administrator flows are implemented in `cliente.php` and `admin.php`.
- No verified testimonials or quantified performance claims were confirmed; do not fabricate them.

## Product Principles

- Make it easy for prospective customers to compare real models and ask about a project.
- Keep customer progress and documents private to the owning account.
- Use confirmed prices, services, contact details, and project evidence accurately.
- Keep the administrator's frequent tasks direct and scannable.