 # Implementation Plan

- [x] 1. Set up database schema and models
  - Create migration for adding is_admin column to users table
  - Create migration for applications table with all required columns and foreign keys
  - Create Application model with relationships, casts, and helper methods
  - Update User model to include is_admin field, application relationship, and isAdmin() method
  - _Requirements: 1.3, 2.3, 2.4, 5.2, 5.3, 5.4, 6.3, 6.4, 7.4, 8.3_

- [x] 2. Create enums for application status values
  - Create ResidencyStatus enum with PENDING, APPROVED, and REJECTED cases
  - Create PartyAffiliation enum with DEMOCRAT, REPUBLICAN, INDEPENDENT, and UNAFFILIATED cases
  - _Requirements: 5.2, 5.3, 6.2, 6.3_

- [x] 3. Implement repository layer for data access
  - Create ApplicationRepository with create, findById, findByVerificationToken, update, delete, and getFiltered methods
  - Implement filtering logic for search, residency status, party affiliation, and email verification status
  - Add eager loading for user, residencyValidator, and partyAssigner relationships
  - _Requirements: 1.3, 2.1, 2.3, 4.1, 4.2, 4.3, 4.4, 7.4, 8.3, 9.2_

- [x] 4. Implement service layer for business logic
  - Create EmailService with sendVerificationEmail method
  - Create ApplicationService with createApplication method that generates verification token and queues email
  - Implement verifyEmail method that validates token expiration and creates user account
  - Implement getFilteredApplications and getApplicationById methods
  - Implement updateApplication and deleteApplication methods with transaction handling
  - Implement updateResidencyStatus method that records validator and timestamp
  - Implement updatePartyAffiliation method that records assigner and timestamp
  - Implement resendVerificationEmail method that generates new token and invalidates old one
  - _Requirements: 1.3, 2.1, 2.3, 2.4, 4.1, 5.2, 5.3, 5.4, 6.3, 6.4, 7.4, 8.3, 9.2, 9.3, 9.5_

- [x] 5. Create email verification mailable
  - Create VerificationEmail mailable class with application property
  - Implement build method that generates verification URL and passes data to view
  - Create email view template with applicant name, instructions, and verification link
  - _Requirements: 2.1, 2.2_

- [x] 6. Implement public registration controller and routes
  - Create ApplicationController with create method to display registration form
  - Implement store method with validation for name, email, and street address
  - Add route for GET /register to show form
  - Add route for POST /register to handle form submission
  - _Requirements: 1.1, 1.2, 1.3, 1.4, 1.5_

- [x] 7. Create public registration form view
  - Create Blade view for registration form with name, email, and street address fields
  - Add CSRF protection and form validation error display
  - Display success message after submission
  - Style form with Tailwind CSS
  - _Requirements: 1.1, 1.2, 1.4, 1.5_

- [x] 8. Implement email verification controller and routes
  - Create VerificationController with verify method that accepts token parameter
  - Add route for GET /verify/{token} to handle verification
  - Create success view for successful verification
  - Create expired view for expired or invalid tokens
  - _Requirements: 2.3, 2.4, 2.5, 2.6_

- [x] 9. Set up authentication system for admin users
  - Configure Laravel authentication scaffolding
  - Create login view with email and password fields
  - Add routes for login (GET and POST) and logout
  - _Requirements: 3.1, 3.2, 3.3, 3.4_

- [x] 10. Create admin middleware for authorization
  - Create AdminMiddleware that checks if user is authenticated and is_admin is true
  - Return 403 Forbidden for unauthorized access
  - Register middleware in bootstrap/app.php
  - _Requirements: 3.5_

- [x] 11. Implement admin application list controller and view
  - Create Admin\ApplicationController with index method
  - Implement filtering by search query, residency status, party affiliation, and email verification
  - Create admin dashboard view with application table showing all relevant fields
  - Add filter form with search input and dropdown filters
  - Add pagination controls
  - Style with Tailwind CSS
  - _Requirements: 4.1, 4.2, 4.3, 4.4_

- [x] 12. Implement admin application detail view and actions
  - Add show method to Admin\ApplicationController
  - Create application detail view displaying all application information
  - Display residency validation controls with approve/reject buttons
  - Display party affiliation dropdown with submit button
  - Show validation and assignment history with admin names and timestamps
  - Add edit and delete buttons
  - Add resend verification email button for unverified applications
  - _Requirements: 4.5, 5.1, 5.5, 6.1, 6.5, 7.1, 8.1, 9.1_

- [x] 13. Implement admin residency validation functionality
  - Add updateResidency method to Admin\ApplicationController
  - Validate residency_status input (approved or rejected)
  - Call ApplicationService to update status with current admin user ID
  - Return to application detail page with success message
  - _Requirements: 5.2, 5.3, 5.4, 5.5_

- [x] 14. Implement admin party assignment functionality
  - Add updateParty method to Admin\ApplicationController
  - Validate party_affiliation input against allowed values
  - Call ApplicationService to update party with current admin user ID
  - Return to application detail page with success message
  - _Requirements: 6.1, 6.2, 6.3, 6.4, 6.5_

- [x] 15. Implement admin application edit functionality
  - Add edit method to Admin\ApplicationController to display edit form
  - Add update method with validation for name, email, and street address
  - Create edit view with pre-filled form
  - Redirect to application detail page after successful update
  - _Requirements: 7.1, 7.2, 7.3, 7.4, 7.5_

- [x] 16. Implement admin application delete functionality
  - Add destroy method to Admin\ApplicationController
  - Add JavaScript confirmation dialog to delete button
  - Call ApplicationService to delete application and associated user
  - Redirect to application list with success message
  - _Requirements: 8.1, 8.2, 8.3, 8.4, 8.5_

- [x] 17. Implement admin resend verification email functionality
  - Add resendVerification method to Admin\ApplicationController
  - Call ApplicationService to generate new token and send email
  - Return to application detail page with success message
  - _Requirements: 9.1, 9.2, 9.3, 9.4, 9.5_

- [x] 18. Create database seeder for initial admin user
  - Create AdminUserSeeder that creates an admin user account
  - Set is_admin to true for the seeded user
  - Add seeder to DatabaseSeeder
  - _Requirements: 3.1, 3.2_

- [x] 19. Configure mail and queue settings
  - Update .env.example with mail configuration variables
  - Configure queue driver to database in config/queue.php
  - Create jobs table migration for queue
  - Document mail setup in README
  - _Requirements: 2.1_

- [x] 20. Add navigation and layout components
  - Create admin layout component with navigation menu
  - Add logout link to admin navigation
  - Create public layout component for registration pages
  - Add consistent styling across all views
  - Remove unused blade templates
  - _Requirements: 3.4, 4.1_

- [x] 21. Implement admin dashboard overview
  - Add dashboard method to Admin\ApplicationController
  - Create getDashboardStats method in ApplicationService
  - Implement repository methods: countByResidencyStatus, countVerifiedAwaitingApproval, countApprovedWithoutParty, countTotal
  - Create admin dashboard view with statistics cards
  - Add navigation links from statistics to filtered application lists
  - Style dashboard with Tailwind CSS
  - Update admin routes to show dashboard as default landing page after login
  - _Requirements: 10.1, 10.2, 10.3, 10.4, 10.5_

- [x] 22. Implement CSV export functionality
  - Add export method to Admin\ApplicationController that returns StreamedResponse
  - Create exportApplicationsToCSV method in ApplicationService
  - Implement getAllForExport method in ApplicationRepository
  - Generate CSV with proper headers and all application data
  - Include timestamps and admin names in export
  - Add export button to application list view
  - Set appropriate CSV headers for file download
  - _Requirements: 11.1, 11.2, 11.3, 11.4, 11.5_
