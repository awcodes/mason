# Contributing

Contributions are **welcome** and will be fully **credited**.

Please read and understand the contribution guide before creating an issue or pull request.

## Etiquette

This project is open source, and as such, the maintainers give their free time to build and maintain the source code
held within. They make the code freely available in the hope that it will be of use to other developers. It would be
extremely unfair for them to suffer abuse or anger for their hard work.

Please be considerate towards maintainers when raising issues or presenting pull requests. Let's show the
world that developers are civilized and selfless people.

It's the duty of the maintainer to ensure that all submissions to the project are of enough
quality to benefit the project. Many developers have different skills, strengths, and weaknesses. Respect the maintainer's decision, and do not be upset or abusive if your submission is not used.

## Viability

When requesting or submitting new features, first consider whether it might be useful to others. Open
source projects are used by many developers, who may have entirely different needs to your own. Think about
whether your feature is likely to be used by other users of the project.

## Procedure

Before filing an issue:

- Attempt to replicate the problem, to ensure that it wasn't a coincidental incident.
- Check to make sure your feature suggestion isn't already present within the project.
- Check the pull requests tab to ensure that the bug doesn't have a fix in progress.
- Check the pull requests tab to ensure that the feature isn't already in progress.

Before submitting a pull request:

- Check the codebase to ensure that your feature doesn't already exist.
- Check the pull requests to ensure that another person hasn't already submitted the feature or fix.

## Development

The repository contains a Workbench — a small Laravel application, powered by
Orchestra Testbench, that consumes Mason the way a real application would. You do
not need a separate Laravel project to work on the package.

Install dependencies:

```bash
composer install
```

Run the test suite:

```bash
composer test
```

Start the Workbench application:

```bash
composer serve
```

The Workbench serves on `http://127.0.0.1:8000`:

| URL | What it exercises |
|---|---|
| `/admin` | The Filament panel. `Mason` on the page form, `MasonEntry` on the view page. |
| `/admin/login` | Prefilled with the seeded development account. |
| `/` | The frontend page list. |
| `/pages/home` | Composed content rendered by the `@mason` Blade directive. |

Development credentials:

```
Email:    test@example.com
Password: password
```

Workbench code lives in `workbench/` and represents the *consuming application*:
its `Page` model, its bricks under `Workbench\App\Mason`, its Filament panel and
its Blade views. Nothing in `workbench/` ships to consumers.

### Workbench assets

Mason has no compiled CSS of its own — consumers import `resources/css/plugin.css`
into their own Filament theme (see the installation docs). The Workbench does the
same, and its two stylesheets are compiled by the Tailwind CLI:

```bash
npm install
npm run build:workbench
```

The compiled output is committed under `workbench/resources/dist/`, in the same way
`resources/dist/` holds the package's compiled JavaScript, so `composer serve`
works from a clean checkout without Node. Rebuild it after changing
`resources/css/plugin.css`, a Mason Blade view, or a Workbench view.

## Requirements

If the project maintainer has any additional requirements, you will find them listed here.

- **[PSR-2 Coding Standard](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md)** – The easiest way to apply the conventions is to install [PHP Code Sniffer](https://pear.php.net/package/PHP_CodeSniffer).

- **Add tests!** — Your patch won't be accepted if it doesn't have tests.

- **Document any change in behavior** – Make sure the `README.md` and any other relevant documentation are kept up to date.

- **Consider our release cycle** – We try to follow [SemVer v2.0.0](https://semver.org/). Randomly breaking public APIs is not an option.

- **One pull request per feature** – If you want to do more than one thing, send multiple pull requests.

- **Send coherent history** – Make sure each commit in your pull request is meaningful. If you had to make multiple intermediate commits while developing, please [squash them](https://www.git-scm.com/book/en/v2/Git-Tools-Rewriting-History#Changing-Multiple-Commit-Messages) before submitting.

**Happy coding**!
