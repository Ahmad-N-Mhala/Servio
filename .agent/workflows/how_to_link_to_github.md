---
description: How to link this project to a GitHub repository
---

This project is not yet a Git repository. Follow these steps to initialize it and push to GitHub.

## 1. Initialize Git

Run the following commands in your terminal to start tracking the project:

```bash
git init
git add .
git commit -m "Initial commit"
```

## 2. Create a Repository on GitHub

1.  Go to [GitHub.com](https://github.com) and log in.
2.  Click the **+** icon in the top right and select **New repository**.
3.  Name your repository (e.g., `Servio`).
4.  **Do not** initialize with README, .gitignore, or License (you already have these).
5.  Click **Create repository**.

## 3. Link and Push

Copy the URL of your new repository (e.g., `https://github.com/yourusername/Servio.git`) and run:

```bash
# Replace <your-repo-url> with the actual URL
git branch -M main
git remote add origin <your-repo-url>
git push -u origin main
```

## 4. Verify

Refresh your GitHub repository page to see your code.
