<?php
require_once 'includes/functions.php';

try {
    $stmt = $pdo->query("
        SELECT c.*, 
            (SELECT image FROM products WHERE category_id = c.id AND image IS NOT NULL AND image != '' LIMIT 1) as image
        FROM categories c 
        ORDER BY name ASC
    ");
    $categories = $stmt->fetchAll();
} catch(PDOException $e) {
    $categories = [];
}

require_once 'includes/header.php';
?>

<div class="container" style="margin-top: 2rem;">
    <div class="dashboard-header" style="background: var(--surface-color); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--border-color); margin-bottom: 2rem;">
        <h1 style="margin: 0; font-size: 2rem;">Browse by Category</h1>
        <p style="color: var(--text-muted); margin-top: 0.5rem; font-size: 1.1rem;">Explore our wide selection of musical instruments and accessories.</p>
    </div>

    <?php if(empty($categories)): ?>
        <div class="alert alert-warning" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 4rem 2rem; background: var(--surface-color); border-color: var(--border-color);">
            <i class="ph ph-folder-open" style="font-size: 4rem; color: var(--text-light); margin-bottom: 1rem;"></i>
            <h3 style="margin-bottom: 0.5rem;">No Categories Found</h3>
            <p style="color: var(--text-muted); margin-bottom: 1.5rem;">We are currently updating our catalog. Please check back later.</p>
            <a href="/melody-masters/shop.php" class="btn btn-primary">View All Products</a>
        </div>
    <?php else: ?>
        <div class="category-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 2.5rem; padding-bottom: 4rem;">
            <?php foreach($categories as $cat): ?>
                <?php $cat_image = !empty($cat['image']) ? $cat['image'] : 'accessories.jpeg'; ?>
                <a href="/melody-masters/shop.php?category=<?php echo $cat['id']; ?>" class="category-card" style="display: flex; flex-direction: column; overflow: hidden; background: var(--surface-color); border-radius: var(--radius-lg); border: 1px solid var(--border-color); text-decoration: none; transition: transform var(--transition-speed) ease, box-shadow var(--transition-speed) ease;">
                    
                    <div class="category-image" style="width: 100%; aspect-ratio: 16/10; overflow: hidden; position: relative; background: var(--bg-color);">
                        <img src="/melody-masters/assets/images/<?php echo htmlspecialchars($cat_image); ?>" alt="<?php echo htmlspecialchars($cat['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;" onerror="this.src='/melody-masters/assets/images/accessories.jpeg'">
                    </div>
                    
                    <div style="padding: 2rem; display: flex; flex-direction: column; flex: 1;">
                        <h3 style="color: var(--primary-color); font-size: 1.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($cat['name']); ?></h3>
                        <?php if(!empty($cat['description'])): ?>
                            <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0 0 1.5rem 0; line-height: 1.5; flex: 1;"><?php echo htmlspecialchars($cat['description']); ?></p>
                        <?php else: ?>
                            <p style="color: var(--text-muted); font-size: 0.95rem; margin: 0 0 1.5rem 0; line-height: 1.5; flex: 1;">Explore our wide selection of <?php echo strtolower(htmlspecialchars($cat['name'])); ?>.</p>
                        <?php endif; ?>
                        
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-light);">
                            <span style="font-weight: 600; color: var(--accent-color); font-size: 0.95rem;">Browse Category</span>
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: rgba(79, 70, 229, 0.1); display: flex; align-items: center; justify-content: center; color: var(--accent-color);">
                                <i class="ph ph-arrow-right"></i>
                            </div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <style>
            .category-card:hover {
                transform: translateY(-5px);
                box-shadow: var(--shadow-lg);
                border-color: var(--border-color); /* Kept subtle */
            }
            .category-card:hover img {
                transform: scale(1.05);
            }
            .category-card:hover .ph-arrow-right {
                transform: translateX(3px);
            }
            .ph-arrow-right {
                transition: transform 0.2s ease;
            }
        </style>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
