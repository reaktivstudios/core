# Code Collaboration

As developers, much of our collaboration happens through code. We have developed a set of conventions, outlined below, that can help when working with version control and on popular repository hosting services like Github. However, when working alongside others on the team always try to:

- Be kind in the feedback that you give
- Be helpful in the requests that you make
- Consider what other developers will need to best help you with your code

## **Git**

Reaktiv uses Git as its code collaboration tool. We generally use GitHub for code storage, sharing, and code reviews. All GitHub repos should begin as private unless otherwise directed. Below we’ll walk through our standard version control workflow for branching, merging and review.

Most of our repos have 3 long-running branches:

**`*main* [or *master*]`** – Gets deployed to production, all branches should start from here.

**`*develop*`** – Gets auto-deployed to staging on each push, never branch from here.

We treat main as if it’s always ready to be deployed to production. Never merge anything into main that couldn’t immediately be deployed.

## **Branching**

The three long-running branches for all projects are the *main*, *staging*, and *develop* branches.

The ***main* branch** is the canonical source of truth. It represents what currently lives in production. This branch is never to be edited or committed to directly. All code changes must be created off of the *main* branch ( or relevant feature branch base ) via pull request branches.

The ***staging*** **branch** is the primary place for client QA. The data, images, and general content for staging should always match *production* as closely as possible. The staging branch should also be reverted nightly to match the *main* branch. Working branches can be merged anytime into the *develop* branch for client QA. Clients should always do QA on the *staging* site, not the *development* site.

The ***develop* branch** is the codebase for a project’s *development* site. The *development* site acts as a sandbox for testing, experimentation, and internal QA. The data and codebase for the *development* site should be reverted nightly to match the *main* branch. This provides developers a place for testing code, PR approval, and editing site content ( content ) in an environment that is not visible to the client. Working branches can be merged anytime into the *develop* branch for experimentation, testing, and QA. The *development* site often contains yet to be approved code, as well as garbage data that is often meant to test edge cases. This can be distracting for clients who need to focus on approving our well defined and nicely scoped tasks.

**Release branches** typically exist for several weeks or months before getting merged into the *main* branch for deployment. Complex releases and long running changes are often referred to as “*release branches”.* It is a good practice to occasionally merge the *main* branch ( or relevant base branch ) into the release branches in order to minimize the number of potential merge conflicts at the completion of the branch’s lifecycle.

Release branches are to be named: **release*/{feature-description}***

**Pull request branching** is a method of encapsulating task-level units of work as defined by our project workflow. Ideally, one project task is worked on as its own pull request branch. When working on project tasks, *always* branch off of either the *main* branch or the relevant long running *feature* branch.

A pull request branch is meant to represent a single task in a project, or a single non-task related update to the code ( *formatting, documentation, plugin updates, etc…* ). Branches can have any number of commits contained in it. As we are a highly collaborative team, it is crucial to push up local branches as often as possible.

**Branch naming conventions** are important because they make things easier to organize, read, and search.

Pull request branches are to be named: **{*type}/{YYYY-MM-DD}-{branch-description}***

```
ex:feature/2022-04-26-add-button-to-contact-form
ex:refactor/2024-11-25-language-from-project-task
```

The **types** of pull request branches are as follows. Most often, it will be *refactor*, *feature*, or *fix*. These types are based on the [commit guidelines for AngularJS](https://github.com/angular/angular.js/blob/master/DEVELOPERS.md#-git-commit-guidelines).

```
❯refactor: A code change that neither fixes a bug nor adds a feature
❯feature: A new feature
❯fix: A bug fix
❯hotfix: An urgent fix that directly corrects a live issue in production
❯docs: Documentation only changes
❯style: Changes that do not affect the meaning of the code (white-space, formatting, missing semicolons, etc)
❯test: Adding missing or correcting existing tests
❯chore: Changes and updates to the build process, plugins, or auxiliary tools and libraries such as documentation generation
```

The **date** is when the branch is created. This ( *somewhat* ) unique identifier helps prevent naming collisions and having to invent creative ways of writing out the same task:

```
refactor/2022-04-11-homepage-styles        refactor/homepage-stylesrefactor/2022-04-14-homepage-styles   vs   refactor/homepage-styles-2refactor/2022-04-15-homepage-styles        refactor/more-homepage-styles
```

The **branch description** should be lowercase alphanumeric characters only ( *no punctuation* ). The language of the description should match the project task as closely as possible. There is no technical length limit, but if the character count of a project task is longer than ~50 characters, consider shortening the branch name using your judgment.

| ✅ | **`refactor/2022-04-11-internationalize-global-footer-phrases`** |
| --- | --- |
| ⛔️ | **`refactor/2022-04-11-internationalize-two-phrases-in-the-global-footer-for-language-sites`** |
| ⛔️ | **`refactor/2022-04-11-internationalize-two-phrases(GlobalFooter)`** |

### Create Branches using Reaktor

Fortunately, we have developed an internal tool that helps to create branches that fit this naming convention. We recommend you use this tool when creating any new branch on a project. With Reaktor installed, run:

| 1 | `reaktor util branch` |
| --- | --- |

This will give you a few prompts:

```
✔ Master branch as base? … [Y for main branch. If using a release branch select n and then search for the name of the release branch]
✔ Branch type: › [chose from the dropdown list]
✔ Branch description: … [branch description]
? Select a date: › [auto populated by default]
```

After that, the command will automatically create a branch off of your selected base using the above naming convention and get you all set and ready to start coding!

## **Pull Requests**

Newly created pull request branches should be pushed to the repository and a corresponding *draft* **pull request** should be created in GitHub. This allows others to have visibility into the work being done and a chance for multidev sites to get spun up ( Pantheon). Creating the pull request at the beginning of the development process is also helpful to the developer by requiring them to understand and internalize the task that needs to be done, how to test it, and how to articulate that to others.

- Be sure to select the correct **base branch** when creating a PR: *main*, or *release/description-text*

**Pull Requests** are to be structured as follows:

```
Title
------
[refactor] - CSS clear for block quotes to prevent the overlap of floating images
```

Pull Requests are to be titled: ****[{type}] – {Task description in sentence case}**

The **type** should correspond to the branch type and be lowercase.

The **task description** should match the project task name as closely as possible.

```
Description + Link to Task
-------------------------------------
Link to Task Ticket

Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.
```

Link to the task’s ticket in the project management software being used for that project. Give a short description of the scope of the task. This is just an overview, the full context for the task should always remain the ticket for the task itself.

```
Steps to Test
------------------
1. Go to ________
2. Meet [x, y, z] conditions
3. Do ______ and validate ______
4. Confirm that ______
5. Look for regressions
```

This list is not prescriptive, but some examples to show the sort of language that is helpful when writing testing instructions. As with task descriptions, being explicit is key. Be specific and precise as to what conditions need to be met in order to test the code. Refer to things semantically and very specifically, so that a user not familiar with the technical lexicon of the particular project could come in and meaningfully test functionality. Point out potential areas of regression to look out for.

```
Screenshots
-----------------
<!-- Insert Screenshots Here -->

Checklists
---------------
[] I have tested this code locally.
[] I have linted this code.
[] I have filled out this pull request.
[] I have added error handling.
[] My code is ready for review.
```

The checklist at the bottom of a pull request is simply a helpful reminder of the basic code practices that we follow.

## Code Review

Code Review is an essential part of the work that we do, and most, if not all, code should be reviewed before it is deployed. Code Reviews go beyond looking at code standards and formatting, but are a chance to highlight potential red flags or use cases that code may not have accounted to, or point to alternative solutions that may be more beneficial overall. Code Reviews are a learning experience and a chance to grow and fortify our code, both for the reviewer and the requester.

We need to respect everyone’s time with regard to code reviews. Often times, code can’t be shipped until it has been reviewed. For that reason, **all PRs that are marked ready for review will be reviewed within 24 hours. If there are changes requested those will be addressed within 24 hours. Approved PRs will be merged where possible within 48 hours or the Monday following if this would trigger a Friday deployment**

You should push your branch up to Github and create a pull request as early as possible in draft form. But when you are ready for the code to be reviewed, fill out your pull request with information in the pull request template, and then tag other team members on the project using Github’s **reviewers** feature.

*If you don’t get a response to your code review after 24 hours, post to the project channel in Slack.*

### Types of Reviews

There are several types of reviews depending on the nature of the code change which affect how the review should be conducted.

### Third party code

These changes often include plugin updates or code that is provided by a third party. During this type of review we often will not be checking every single line of code. Instead the goal is to focus on the files being changed.

For example: A retainer project has a monthly plugin update task. The PR would include a table of what plugins were updated, the old version, and the new version.

[](https://lh4.googleusercontent.com/WHpqQxhfqA95ozn5VVEdlxhuO3WWdTOjbMZW_HwWp-lvZA_YlKmXnCLWn95r14ES8Q8e8ysDzXteO3vBrDJIHioxDKa9FJKdASN2hCJ4-AZ5Xtl8AMbGaqzSKV3sK3-VgDKE0YD67SQQaalPlT4rCYMtRbMVu26ZMhqrnVU6DSKO7I5WciMJjmsk)

The reviewer could then check to make sure that only those plugin files were updated using the tree view on the side of the files to check which folders were updated. The reviewer may also offer input if they are aware of conflicts those updates have resulted in in the past such as, “this plugin can’t be updated because it will stop the amp-story feature from working” or “when we updated wp-seo on another site it broke at WordPress 5.8.”

### Non-Code changes

Updates to readme files and other non-code changes should be checked for grammar, spelling, and clarity. The most important security issue to check would be inclusion of API keys or other private data.

### CSS only changes

Often updates include style changes. The security risks are very low. The most important things to check are optimization and use of existing components.

Overly complex CSS selectors, use of !important, and multiple overrides resulting in more generated CSS than may be necessary are worth flagging. Some projects may also have specific requirements for variable use and handling that affect the optimization of the generated CSS.

Finally, we have a lot of components that can be used so creating a new component that is effectively the same as an existing component with minimal change can result in a lot of additional generated CSS, which affects load time.

### Questions to ask when reviewing code

Code reviewers should be answering the following questions:

- Does the code comply with your project’s identified coding standards?
- Does the code limit itself to the scope identified in the ticket?
- Does the code actually address the scope of work in the ticket?
- Does the code follow industry best practices in the most efficient way possible?
- Are there possible side effects to the code that have not been addressed?
- Where might the code be more efficient, performant, or accessible?
- Has the code been implemented in the best possible way according to all of your internal “bug-a-boos?” (It’s important to separate your preferences and stylistic differences from actual problems with the code).

One of the most overlooked of these is the second question “does the code limit itself to the scope identified in the ticket?” Code that includes code standard updates for the entire file obfuscates the actual change and makes it more difficult to identify the actual changes, which increases the risk that important issues will be missed. The reviewer should request a change that results in the code standard changes that are not relevant to the ticket be moved to a seperate PR. The base branch for the PR can be updated to point to the new PR with the code standard changes, which will expose the actual scope of work being changed.

This also affects when a PR includes multiple features and fixes, which affects the ability to do a review of a given feature.

*The third question, “Does the code actually address the scope of work in the ticket?” means the reviewer will need to look at the ticket to understand the scope of work, and follow the testing notes the PR outlines to ensure that the feature or fix works as described.*

## PR Review Links

Here are a list of links that can help identify PRs that need review, have requested changes, are in draft, or otherwise need your attention.

| Link | Description |
| --- | --- |
| [All of your open PRs](https://github.com/pulls?q=is%3Aopen+is%3Apr+author%3A%40me+archived%3Afalse+) | Any of your PRs that are open including draft PRs. |
| [All of your open PRs that are not draft](https://github.com/pulls?q=is%3Aopen+is%3Apr+author%3A%40me+archived%3Afalse+draft%3Afalse+) | Any of your PRs that are open excluding draft PRs. |
| [All of your draft PRs](https://github.com/pulls?q=is%3Aopen+is%3Apr+author%3A%40me+archived%3Afalse+draft%3Atrue+) | Only your open, draft PRs. |
| [PRs assigned to you](https://github.com/pulls?q=is%3Aopen+is%3Apr+archived%3Afalse+draft%3Atrue+assignee%3A%40me+) | This should be PRs that you’ve started a review on, requested changes for, or otherwise are involved in. |
| [All PRs you’ve been requested to review](https://github.com/pulls?q=is%3Aopen+is%3Apr+archived%3Afalse+review-requested%3A%40me+no%3Aassignee+) | All PRs you’ve been requested to review that are not assigned to anyone. |
| [Non draft PRs you’ve been requested to review](https://github.com/pulls?q=is%3Aopen+is%3Apr+archived%3Afalse+draft%3Afalse+review-requested%3A%40me+no%3Aassignee) | All PRs you’ve been requested to review that are not assigned to anyone and are not drafts. |
| [All your PRs with requested changes](https://github.com/pulls?q=is%3Aopen+is%3Apr+archived%3Afalse+review%3Achanges-requested+author%3A%40me+) | All the PRs you created that have requested changes. |
| [All your PRs with requested changes excluding drafts](https://github.com/pulls?q=is%3Aopen+is%3Apr+archived%3Afalse+review%3Achanges-requested+author%3A%40me+draft%3Afalse) | All the PRs you created except drafts that have requested changes. |