@echo off
echo ========================================
echo Transformation de aymand en ADMIN
echo ========================================
php bin/console dbal:run-sql "UPDATE utilisateur SET roles = '[\"ROLE_ADMIN\"]' WHERE id_utilisateur = 'aymand'"
echo.
echo ========================================
echo Verification du role:
echo ========================================
php bin/console dbal:run-sql "SELECT id_utilisateur, roles FROM utilisateur WHERE id_utilisateur = 'aymand'"
echo.
echo TERMINE! aymand est maintenant ADMIN
pause
