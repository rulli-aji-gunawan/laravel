@echo off
echo ============================================
echo Railway Project Quick Commands (No Admin)
echo ============================================
echo.

echo 1. Check Git Status
git status
echo.

echo 2. Available commands:
echo    - git add .
echo    - git commit -m "Your message"
echo    - git push origin main
echo.

echo 3. Check if changes are ready to push:
git diff --cached --stat
echo.

echo 4. To deploy changes:
echo    a) git add .
echo    b) git commit -m "Update: description of changes"
echo    c) git push origin main
echo.

echo 5. Railway will auto-deploy in 2-3 minutes after push
echo.

echo ============================================
echo Current Railway App Status:
echo Check your Railway dashboard at:
echo https://railway.app
echo ============================================

pause
