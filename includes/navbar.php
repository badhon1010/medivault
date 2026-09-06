<nav class="bg-white shadow-md sticky top-0 z-50 w-full">
    <div class="container mx-auto px-4">
        <div class="flex justify-between items-center h-20">
            <a href="dashboard.php" class="flex items-center gap-2 group">
                <div class="bg-arogga text-white p-2 rounded-lg group-hover:rotate-12 transition-transform duration-300">
                    <i class="fas fa-heartbeat text-xl"></i>
                </div>
                <span class="text-2xl font-bold text-slate-800 tracking-tight">Medi<span class="text-arogga">Vault</span></span>
            </a>

            <?php if(isset($show_search) && $show_search): ?>
            <div class="hidden md:flex flex-1 max-w-2xl mx-8 items-center gap-3 relative group">
                <div class="flex-1 relative">
                    <input type="text" id="searchInput" placeholder="Search medicines..." 
                           class="w-full pl-5 pr-12 py-3 bg-gray-100 border border-transparent rounded-full focus:ring-2 focus:ring-teal-500 focus:bg-white focus:border-teal-500 transition-all duration-300 text-sm font-medium outline-none shadow-sm group-hover:shadow-md">
                    <button class="absolute right-2 top-1.5 bg-arogga text-white w-9 h-9 rounded-full flex items-center justify-center hover:bg-teal-700 hover:scale-110 transition-all duration-300 shadow-sm">
                       <i class="fas fa-search text-xs"></i>
                    </button>
                </div>
                <?php if(isset($categories)): ?>
                <select id="categoryFilter" class="bg-gray-100 border border-transparent rounded-full px-4 py-3 text-sm font-medium text-slate-600 outline-none focus:ring-2 focus:ring-teal-500 focus:bg-white focus:border-teal-500 transition-all duration-300 shadow-sm">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $category): ?>
                       <option value="<?= (int) $category['category_id'] ?>"><?= htmlspecialchars($category['category_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="hidden md:flex flex-1"></div>
            <?php endif; ?>

            <div class="flex items-center gap-6">
                <div class="hidden lg:flex items-center gap-6 font-semibold text-gray-500 text-sm">
                    <a href="upload_prescription.php" class="hover:text-arogga transition-colors flex items-center gap-1 hover:scale-105 transform duration-200">
                        <i class="fas fa-file-upload"></i> Upload
                    </a>
                    <a href="view_prescriptions.php" class="hover:text-arogga transition-colors flex items-center gap-1 hover:scale-105 transform duration-200">
                        <i class="fas fa-images"></i> Gallery
                    </a>
                    <a href="order_history.php" class="hover:text-arogga transition-colors flex items-center gap-1 hover:scale-105 transform duration-200">
                        <i class="fas fa-history"></i> Orders
                    </a>
                    <a href="my_appointments.php" class="hover:text-arogga transition-colors flex items-center gap-1 hover:scale-105 transform duration-200">
                        <i class="fas fa-calendar-check"></i> Appointments
                    </a>
                    <a href="support_ticket.php" class="hover:text-arogga transition-colors flex items-center gap-1 hover:scale-105 transform duration-200">
                        <i class="fas fa-headset"></i> Support
                    </a>
                    <a href="profile.php" class="hover:text-arogga transition-colors flex items-center gap-1 hover:scale-105 transform duration-200">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </div>
                
                <?php if(isset($show_cart) && $show_cart): ?>
                <button onclick="toggleCart(true)" class="relative bg-teal-50 text-arogga p-3 rounded-xl hover:bg-arogga hover:text-white transition-all active:scale-90">
                    <i class="fas fa-shopping-bag text-xl"></i>
                    <span id="cart-badge" class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] font-black w-5 h-5 flex items-center justify-center rounded-full shadow-lg border-2 border-white" style="display:none;">0</span>
                </button>
                <?php endif; ?>

                <div class="flex items-center gap-3 border-l pl-6 border-gray-200">
                    <div class="text-right hidden sm:block">
                        <p class="text-[10px] text-gray-400 font-bold uppercase">Hello,</p>
                        <p class="text-xs font-bold text-slate-700"><?= htmlspecialchars($_SESSION['user_name'] ?? 'User') ?></p>
                    </div>
                    <a href="logout.php" class="text-red-400 hover:text-white hover:bg-red-500 bg-red-50 p-2 rounded-full transition-all duration-300" title="Logout">
                        <i class="fas fa-power-off"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>
