# VanService Implementation Checklist

## October 2026 System Update

- [x] Restrict the user dashboard main trip list to active and pending trips only.
- [x] Detect conflicting vehicle bookings and show a clear availability prompt before booking.
- [x] Preserve and validate refueling records by van and optional trip.
- [x] Add daily, monthly, and yearly fuel reports with printable and CSV exports.
- [x] Add selectable fuel-consumption graphs for daily, monthly, and yearly monitoring.
- [x] Add an authorized printable passenger trip ticket with complete trip and booking details.
- [x] Add deploy-ready web hosting configuration and practical online deployment guidance.
- [x] Validate routes, PHP syntax, Blade rendering, and package the complete update.

## Bytehost Deployment

- [x] Verify Bytehost's current PHP, MySQL, SSH, document-root, and Laravel deployment support.
- [x] Add Bytehost-specific deployment files and installation instructions.
- [x] Build and validate an FTP-ready Bytehost upload package with protected production settings.
- [x] Build a domain-configured Bytehost installation package with a one-time MySQL schema installer.
- [ ] Replace the initial Bytehost settings file with the corrected one-time installer token configuration.
- [ ] Replace the public Bytehost installer with the version that prepares its Laravel runtime folders automatically.
- [ ] Run the protected Bytehost MySQL diagnostic and resolve the reported connection error.
- [ ] Guide the user through the remaining VistaPanel upload, database-password entry, import, and public URL test steps.
- [ ] Deploy the application to the user's Bytehost account after the user authorizes the account connection and provides the hosting details required by Bytehost.

## Alternative Test Hosting

- [ ] Evaluate an alternative host that can run the Laravel application and database for online testing.
- [ ] Prepare the selected host's deployment configuration and deploy after the user authorizes account access.

## NISU Campus Trip Locations

- [ ] Add NISU campus dropdowns for trip and booking pickup/drop-off locations.
- [ ] Display the selected campus route in the trip details and printable ticket.

## XAMPP Release Package

- [x] Build and verify a current XAMPP-ready Laravel source package with dependencies and setup instructions.
- [x] Remove development-only providers from the production XAMPP release and rebuild the archive.

## NISU Driver's Trip Ticket

- [x] Add an official-style A4 NISU driver's trip ticket with driver, van, route, passenger, distance, fuel, takeoff, and arrival fields.
- [x] Add admin and driver dashboard print links after bookings are recorded.
- [x] Add native driver app trip-ticket display and authenticated Laravel API response.
