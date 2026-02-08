<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ForumCategory;
use App\Models\ForumPost;
use App\Models\ForumReply;
use App\Models\Doctor;

class ForumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Forum Categories
        $categories = [
            [
                'name' => 'Dermatology',
                'slug' => 'dermatology',
                'description' => 'Discuss skin conditions, treatments, and dermatological procedures',
                'icon' => '🧴',
                'color' => '#8b5cf6', // purple
                'order' => 1,
            ],
            [
                'name' => 'Cardiology',
                'slug' => 'cardiology',
                'description' => 'Heart health, cardiovascular diseases, and treatments',
                'icon' => '❤️',
                'color' => '#ef4444', // red
                'order' => 2,
            ],
            [
                'name' => 'General Practice',
                'slug' => 'general-practice',
                'description' => 'General medical topics and family medicine',
                'icon' => '🏥',
                'color' => '#3b82f6', // blue
                'order' => 3,
            ],
            [
                'name' => 'Telemedicine',
                'slug' => 'telemedicine',
                'description' => 'Virtual care best practices and technology',
                'icon' => '💻',
                'color' => '#10b981', // green
                'order' => 4,
            ],
            [
                'name' => 'Policy & Guidelines',
                'slug' => 'policy-guidelines',
                'description' => 'Medical regulations, insurance, and professional guidelines',
                'icon' => '📋',
                'color' => '#f59e0b', // amber
                'order' => 5,
            ],
            [
                'name' => 'Research & Studies',
                'slug' => 'research-studies',
                'description' => 'Latest medical research and clinical trials',
                'icon' => '🔬',
                'color' => '#06b6d4', // cyan
                'order' => 6,
            ],
            [
                'name' => 'Professional Development',
                'slug' => 'professional-development',
                'description' => 'Career growth, continuing education, and certifications',
                'icon' => '📚',
                'color' => '#ec4899', // pink
                'order' => 7,
            ],
        ];

        foreach ($categories as $categoryData) {
            ForumCategory::create($categoryData);
        }

        // Get some doctors for sample posts
        $doctors = Doctor::where('is_approved', true)->take(5)->get();
        
        if ($doctors->count() === 0) {
            // If no doctors exist, we can't create sample posts
            $this->command->info('No approved doctors found. Skipping sample post creation.');
            return;
        }

        // Create Sample Posts
        $samplePosts = [
            [
                'title' => 'Best Practices for Treating Adult Acne with Hormonal Factors',
                'content' => '<p>I\'ve been seeing an increasing number of adult female patients presenting with persistent acne that doesn\'t respond well to traditional treatments. Many of these cases seem to have hormonal underlying factors.</p><p>What are your experiences with managing hormonal acne in adults? Do you typically start with topical treatments or move directly to hormonal therapy? I\'d love to hear your protocols and success rates.</p><p>Specifically interested in:</p><ul><li>First-line treatment options</li><li>When to consider spironolactone</li><li>Managing expectations with patients</li><li>Long-term management strategies</li></ul>',
                'category' => 'dermatology',
                'tags' => ['acne', 'hormonal', 'adult-acne', 'treatment'],
                'views' => 342,
                'is_pinned' => true,
            ],
            [
                'title' => 'Updated Guidelines for Telemedicine Reimbursement in Nigeria',
                'content' => '<p>The National Health Insurance Scheme (NHIS) has recently updated their guidelines regarding telemedicine consultations and reimbursement rates.</p><p>Key Changes:</p><ul><li>Virtual consultations now eligible for 80% reimbursement rate</li><li>Documentation requirements have been updated</li><li>New CPT codes for video consultations</li></ul><p>Has anyone had experience claiming under these new guidelines? What has been your success rate with reimbursement?</p>',
                'category' => 'policy-guidelines',
                'tags' => ['telemedicine', 'insurance', 'nhis', 'reimbursement'],
                'views' => 2456,
                'is_pinned' => false,
            ],
            [
                'title' => 'Managing Hypertension in Young Adults: A Growing Concern',
                'content' => '<p>I\'ve noticed a troubling trend in my practice - more patients in their 20s and 30s presenting with hypertension. This seems to be correlated with lifestyle factors including:</p><ul><li>High stress levels from work</li><li>Poor dietary habits</li><li>Sedentary lifestyle</li><li>Inadequate sleep</li></ul><p>How are you approaching lifestyle modifications in this demographic? What strategies have been most effective in achieving patient compliance?</p>',
                'category' => 'cardiology',
                'tags' => ['hypertension', 'young-adults', 'lifestyle', 'prevention'],
                'views' => 187,
                'is_pinned' => false,
            ],
            [
                'title' => 'Tips for Effective Virtual Consultations: What Works for You?',
                'content' => '<p>After conducting hundreds of telemedicine consultations, I\'ve developed some best practices that significantly improve patient satisfaction and outcomes.</p><p>My Top Tips:</p><ol><li><strong>Environment Setup:</strong> Ensure good lighting and minimal background distractions</li><li><strong>Technology Check:</strong> Always test audio/video 5 minutes before appointment</li><li><strong>Patient Engagement:</strong> Ask patients to position camera at eye level</li><li><strong>Documentation:</strong> Share screen to review treatment plans with patients</li></ol><p>What are your best practices for telemedicine? Let\'s share insights!</p>',
                'category' => 'telemedicine',
                'tags' => ['best-practices', 'virtual-care', 'patient-satisfaction'],
                'views' => 562,
                'is_pinned' => true,
            ],
            [
                'title' => 'Recent Study: Impact of COVID-19 on Long-term Cardiovascular Health',
                'content' => '<p>A new study published in the Journal of Cardiology shows interesting findings about long-term cardiovascular effects in COVID-19 survivors.</p><p>Key Findings:</p><ul><li>Increased risk of myocarditis even in mild cases</li><li>Elevated D-dimer levels persisting 6+ months post-infection</li><li>Higher incidence of arrhythmias in recovered patients</li></ul><p>This has implications for our practice. Should we be screening all COVID-19 survivors for cardiovascular complications? What are your thoughts?</p>',
                'category' => 'research-studies',
                'tags' => ['covid-19', 'cardiology', 'long-covid', 'research'],
                'views' => 891,
                'is_pinned' => false,
            ],
            [
                'title' => 'Continuing Medical Education: Recommended Courses for 2026',
                'content' => '<p>With the new year upon us, I\'m planning my CME activities. What courses or certifications are you prioritizing this year?</p><p>I\'m particularly interested in:</p><ul><li>Telemedicine best practices certification</li><li>Advanced wound care management</li><li>Point-of-care ultrasound</li></ul><p>Please share your recommendations and experiences with various CME providers!</p>',
                'category' => 'professional-development',
                'tags' => ['cme', 'education', 'certification', '2026'],
                'views' => 234,
                'is_pinned' => false,
            ],
        ];

        foreach ($samplePosts as $postData) {
            $category = ForumCategory::where('slug', $postData['category'])->first();
            $doctor = $doctors->random();
            
            $post = ForumPost::create([
                'doctor_id' => $doctor->id,
                'category_id' => $category->id,
                'title' => $postData['title'],
                'content' => $postData['content'],
                'tags' => $postData['tags'],
                'is_pinned' => $postData['is_pinned'],
                'is_published' => true,
                'views_count' => $postData['views'],
                'last_activity_at' => now()->subHours(rand(1, 48)),
            ]);

            // Add some replies to posts
            $replyCount = rand(2, 15);
            for ($i = 0; $i < $replyCount; $i++) {
                $replyDoctor = $doctors->random();
                
                $replyContents = [
                    '<p>Great question! In my practice, I typically start with topical retinoids and benzoyl peroxide before considering systemic options.</p>',
                    '<p>I\'ve had excellent results with this approach. Patient compliance has been around 75% in my experience.</p>',
                    '<p>This aligns with what I\'ve been seeing in my clinic as well. Thanks for sharing these insights!</p>',
                    '<p>Have you considered adding oral antibiotics as a bridging therapy? I\'ve found this helpful in some cases.</p>',
                    '<p>Interesting perspective. I\'d also recommend checking thyroid function in these patients.</p>',
                    '<p>Thanks for starting this discussion. Very timely and relevant to my current practice.</p>',
                    '<p>I\'ve been following similar protocols with good outcomes. Key is patient education and setting realistic expectations.</p>',
                    '<p>This is exactly what I needed to read today. Have you published any case studies on this?</p>',
                ];
                
                ForumReply::create([
                    'post_id' => $post->id,
                    'doctor_id' => $replyDoctor->id,
                    'content' => $replyContents[array_rand($replyContents)],
                    'created_at' => now()->subHours(rand(0, 47)),
                ]);
            }
        }

        $this->command->info('Forum seeded successfully with categories and sample posts!');
    }
}
