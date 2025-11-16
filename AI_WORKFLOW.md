# AI Workflow Documentation

## 1. Where I Used AI

* Backend architecture design (Service layer, Events, Redis cache)
* Initial skeleton code for REST API endpoints
* Initial version of Dashboard and Attendance UI using Vue 3 Composition API
* Writing the initial draft of README / AI_WORKFLOW
* Took the full Docker setup from ChatGPT

Primary tools:

* **ChatGPT (GPT-5.1 Thinking)** – architecture + code suggestions
* **VS Code Chat (Copailot)** – minor refactors and bug fixes

## 2. Three Specific Prompts Used

1. **Prompt:**
   "Build a Mini School Attendance System with Laravel 12 and Vue 3, including service layer, Redis caching, and monthly report command. Give me clean structure examples."
   **Helped:**

   * To plan the high-level structure of the entire system.

2. **Prompt:**
   "Write a Laravel 12 AttendanceService with bulkRecord, dailyStats (Redis cached), and monthlyReport using eager loading."
   **Helped:**

   * To get a clean service-layer implementation.

3. **Prompt:**
   "Create a Vue 3 Composition API Attendance page where I can load students by class and mark Present/Absent/Late with bulk actions and live percentage."
   **Helped:**

   * Allowed me to build the Attendance UI quickly.

## 3. How AI Improved Development Speed

* Saved time in architecture planning (chose the best idea from AI variants)
* Repetitive boilerplate (controller, request, resource) was generated quickly
* Got bug-fix suggestions and refactor ideas

Overall, it reduced development time by approximately **30–40%**.

## 4. What Code Was Manual vs AI-Generated

### AI-assisted parts

* AttendanceService skeleton
* Controller method signatures and basic logic
* Initial layout of Vue 3 views
* Initial drafts of README and AI_WORKFLOW

### Manually written / heavily edited

* Validation rules and business-rule tuning
* Query optimization and Redis cache key strategy
* Tests (Pest) — written manually based on project context
* Deployment and environment-specific configurations
