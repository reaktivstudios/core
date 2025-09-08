# Contributing

The following is a set of guidelines meant to apply across the Reaktiv suite of projects, which follow similar conventions and processes. This gives an overview of how to contribute, and where to submit issues. It also outlines our expectatoins for contributors.

For more information about Reaktiv products and best practices visit our documentation site:

## Reporting bugs

To report a bug or issue on any plugin, please first refer to the existing issues list first. If you find a similiar issue, you can note your particular use case there. Otherwise you can open a new issue directly in Github.

When reporting a bug, please include steps to reproduce the issue, including browser and device, environment details, and software versions. If possible, also include screenshots to visualize the issue.

### Suggesting features and changes

New features and changes are managed through issues as well. Please include a description of the issue and potential solutions if possible.

### Pull requests

Pull requests should solve a specific problem tied to an issue. Please make sure an issue has been discussed and accepted before moving to a pull request.

Each repo contains a pull request template which should be filled out before submitting a pull request to ensure acceptance criteria is met.

## Workflow

The `develop` branch is the development branch which means it contains the next version to be released. `stable` contains the current latest release and `trunk` contains the corresponding stable development version. Always work on the `develop` branch and open up PRs against `develop`.

## Release instructions

1. Branch: Starting from `develop`, create a release branch named `release/X.Y.Z` for your changes.
2. Follow pull request checklist: A draft release pull request will be created once you push your branch to GitHub. Follow the steps in the pull request.

Should the pull request fail to be created, a pull request can be manually created using the [template file](https://github.com/10up/distributor/blob/develop/.github/release-pull-request-template.md) containing each of the steps.