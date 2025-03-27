# AI Agent Prompt Guideline for TALL Stack Projects

## Direct AI Prompt Context
You are an AI assistant generating code for a TALL Stack (Tailwind, Alpine.js, Laravel, Livewire) project. Follow these principles and best practices strictly to ensure high-quality, maintainable, and production-ready code.

## General Principles
1. **Maintain Simplicity (KISS)**
   - Code should be easy to read and understand.
   - Avoid unnecessary complexity; prioritize clarity.
   - Prefer explicit, self-explanatory function and variable names.

2. **Avoid Repetition (DRY)**
   - Reuse existing functions, components, and utilities.
   - Extract repetitive logic into reusable classes, traits, or components.
   - Minimize duplication in frontend and backend logic.

3. **Follow the Open-Closed Principle**
   - Design code to be extendable without modifying existing logic.
   - Use dependency injection and service providers where applicable.
   - Favor configuration over hardcoding values.

## Code Structure and Organization
### Backend (Laravel)
- **Controllers**
  - Controllers should be thin and delegate logic to Services or Actions.
  - Follow RESTful conventions.
  
- **Models**
  - Use Eloquent relationships efficiently.
  - Keep business logic out of models; use repositories or service classes instead.
  - Utilize accessors and mutators when necessary.
  
- **Services and Repositories**
  - Business logic should reside in Services.
  - Use Repositories for database interactions when needed.
  
- **Livewire Components**
  - Keep components small and focused.
  - Use computed properties over unnecessary event-driven state changes.
  - Maintain clear separation between public and private properties/methods.
  
- **Facades**
  - Utilize Laravel’s built-in Facades (e.g., `Cache::`, `Log::`, `Storage::`) where appropriate.
  - Avoid excessive use of Facades in business logic; use dependency injection when necessary.

- **Filament Admin Panel**
  - Use Filament for efficient admin dashboard generation.
  - Leverage Filament resources, widgets, and forms to streamline CRUD operations.
  - Extend Filament’s functionality through custom actions and pages.

### Frontend (Tailwind, Alpine.js, Livewire)
- **Tailwind CSS**
  - Use utility classes effectively.
  - Avoid unnecessary custom CSS; extend Tailwind when needed.
  
- **Alpine.js**
  - Use Alpine for simple interactivity, but Livewire for complex interactions.
  - Keep Alpine state minimal and component-scoped.
  
### API and Security Considerations
- **Validation**
  - Always validate input using Laravel Form Requests or inline validation.
  - Use meaningful validation rules and messages.
  
- **Authentication & Authorization**
  - Use Laravel’s built-in authentication features.
  - Implement authorization using Policies and Gates.
  
- **API Design**
  - Follow RESTful API principles.
  - Use Laravel’s API Resource classes for consistent JSON responses.
  
## Coding Standards
- **PSR-12 compliance** for PHP.
- **Consistent naming conventions**:
  - CamelCase for class names (`MyService`)
  - snake_case for database columns and variables (`user_email`)
  - kebab-case for route names (`user-profile.edit`)
- **Comment when necessary**, but prefer self-explanatory code.
- **Use environment variables (`.env`)** for sensitive data.

## Expected Output Quality
- AI-generated code should be **production-ready** with minimal refactoring.
- Code should be **efficient**, avoiding unnecessary loops, queries, or large payloads.
- Follow **Laravel best practices** (use queues, caching, and eager loading when applicable).
- Utilize **Filament** for backend admin management when necessary.
- The final output should be **formatted and linted properly**.

## Conclusion
Following this guideline will ensure that all AI-generated code is consistent, maintainable, and adheres to best practices in TALL Stack development. Use this as a standard reference for evaluating AI-generated solutions.
