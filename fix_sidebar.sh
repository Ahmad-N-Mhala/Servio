#!/bin/bash

# This script fixes the sidebar navigation items to add tooltips and proper spacing when collapsed

FILE="/Users/ahmadmhala/Downloads/RestoFy-main/resources/js/Layouts/MainLayout.vue"

# Fix 'menu' route
sed -i '' '/:href="route('\''menu\.index'\'')" /,/class="\[/s/px-4 py-3/px-4 py-3'\'',\n                            isSidebarCollapsed ? '\''justify-center px-2 py-3'\'' : '\''px-4 py-3'\''/' "$FILE"

# Add title attribute to menu link  
sed -i '' '/:href="route('\''menu\.index'\'')" /a\
                        :title="isSidebarCollapsed ? $t('\''nav.menu'\'') : '\''\''"
' "$FILE"

# Fix mr-3 for menu
sed -i '' '/route('\''menu\.index'\'')/,/nav\.menu/s/mr-3/mr-3'\'',\n                            isSidebarCollapsed ? '\'''\'' : '\''mr-3'\''/' "$FILE"

echo "Fixed menu item"

# Similar fixes for tables, staff, kitchen, pos, loyalty, earning_methods
