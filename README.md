🍲 Web-Based Food Ordering System - BSLH-FOS 🚀
1. Project Overview and Executive Summary
   

The Bente Sais Lomi House Food Ordering System (BSLH-FOS) is a comprehensive web-based application developed to modernize and manage the operational workflow of Bente Sais Lomi House. Launched in November 2025, this system transitions the restaurant from manual processes—such as paper-based order logging and verbal communication—to a streamlined, digitized platform.

The core objective is to enhance efficiency, reduce human errors in order taking and payment calculation, and provide superior service to customers. The system supports secure, role-based access for customers, staff, and administrators.

1.1 Project Brief

The BSLH-FOS serves as a centralized management tool for the restaurant:

Customers

Browse the menu

Place orders electronically

Confirm payments

Track order status in real-time

Staff

Manage incoming orders

Update preparation and delivery statuses

Verify payments

Administrators

Manage menu items

Handle user accounts

Monitor restaurant operations

Generate analytical reports

1.2 Project Scope

The system serves three user account types:
Customers, Staff, and Administrators

Role	In-Scope Activities
Customers	Registration, login, browsing menu, selecting items, placing orders, confirming payments, tracking orders
Staff	Viewing/confirming orders, updating status, verifying payments, marking orders as completed
Administrators	Managing menu items, user accounts, order monitoring, generating reports
Key External Dependencies

PayMongo API – secure online payment processing

Geocoding API – converting addresses into coordinates

Exclusions / Current Limitations

No integration with Grab/Foodpanda

No automated delivery routing

No loyalty or rewards program

💻 2. Technology Stack and Operating Environment

The system is developed using a robust web stack.

2.1 Technology Stack
Component	Technology	Description
Backend	PHP	Business logic, server-side processing
Frontend	HTML, CSS, JS	Interface and client-side interaction
Styling Framework	Bootstrap	Responsive, mobile-friendly UI
Database	MySQL / MariaDB	Persistent relational data storage
Payment	PayMongo API	Secure payment transactions
Location	Geocoding API	Converts address to coordinates
2.2 Operating Environment

Platform: Web-based application

Client Access: Chrome, Firefox, Edge (desktop/mobile)

Server OS: Windows or Linux

Connectivity: Internet required for PayMongo + Geocoding

Database: MySQL / MariaDB

⚙️ 3. Software Requirement Specification (SRS)
3.1 Functional Requirements (FRs)
3.1.1 User Authentication & Authorization

FR-1: Login for customers, staff, admins

FR-2: Customer registration

FR-3: Validate user credentials

FR-4: Enforce RBAC

FR-5: Secure logout

3.1.2 Customer Interaction

FR-6 & FR-7: Update profile details

FR-8: Store/display default address

FR-9 & FR-10: View menu

FR-11 & FR-12: Search/filter menu

3.1.3 Cart and Order Placement

FR-13–14: Add/remove/modify cart items

FR-15: Compute totals

FR-16–17: Checkout with fulfillment options

FR-18: Save orders with “Pending” status

3.1.4 Payment Processing

FR-19–20: Online payment via PayMongo

FR-21–22: Record transaction + update status

FR-23: Staff can mark manual payments

3.1.5 Order Tracking (Customer)

FR-24–26: View orders

FR-25–27: View/update order status

3.1.6 Order Management (Staff)

FR-28–29: View all active orders

FR-30–31: Update status with timestamps

FR-32: Verify payment before marking ready/completed

3.1.7 Menu Management (Admin)

FR-33–34: Add/edit menu items

FR-35: Deactivate/reactivate items

FR-36 (Optional): Upload item images

3.1.8 Reporting (Admin)

FR-41–42: Sales summaries

FR-43: Top ordered items

FR-44 (Optional): Export to CSV

3.1.9 Location Tracking

FR-45: Input address

FR-46: Use Geocoding API

FR-47: Save coordinates

3.2 Non-Functional Requirements (NFRs)
3.2.1 Performance

NFR-1: 10–20 concurrent users

NFR-2: Page loads ≤ 3 seconds

NFR-3: Order processing ≤ 5 seconds

3.2.2 Security

NFR-4: Login required

NFR-5: Password hashing

NFR-6: Protect against SQLi, XSS

NFR-7: Strict RBAC

NFR-8: Keep API keys secure

3.2.3 Usability

NFR-9: Simple, intuitive

NFR-10: Clear labels

NFR-11: Responsive, mobile-friendly

3.2.4 Maintainability / Portability

NFR-15–17: Modular code, secure config files, standard hosting compatible

📐 4. System Architecture and Design
4.1 Architecture Layers

Frontend: HTML + CSS + Bootstrap

Backend: PHP (business logic)

Database Layer: MySQL/MariaDB

4.2 Use Case Diagram

Actors: Customer, Staff, Admin

UC-07: Checkout

UC-10: View Orders Queue

UC-14: View Dashboard

UC-17: Manage Menu

4.3 Data Flow Diagram (DFD)

Context Diagram (Level 0)
Shows data flow between Customer, Staff, Admin, and the Food Ordering System.

4.4 Entity Relationship Diagram (ERD)
Core Entities

users

categories

products

orders

order_items

order_payment_details

order_addresses

Example (orders table)
Column	Type	Description
order_number	varchar(20)	Reference number
status	enum	Order stage
total_amount	decimal	Final amount
handler_id	int	Staff assigned
confirmed_at	datetime	Timestamp
🤝 5. Group Participation and Development Team
Team Member	Responsibility
Banto, Saipoden D.	DFDs
Baquiran, John Aldrie T.	DFDs
Bugtong, Jhayvic D.	ERD
Catibog, Kier D.	SRS
Ortega, Aeron Cedric T.	Use Case Diagrams

























































































>
























































.





















.




































.































































































.












































































































































.










































.




















































.
















































.
























































.





























































.






























































.























































.




























































.

