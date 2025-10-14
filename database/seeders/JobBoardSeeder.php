<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Job;
use Illuminate\Database\Seeder;

class JobBoardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample companies
        $companies = [
            [
                'name' => 'GitHub',
                'slug' => 'github',
                'description' => 'GitHub is the world\'s leading software development platform, empowering millions of developers to build, ship, and maintain their software.',
                'industry' => 'Technology',
                'size' => '500+',
                'headquarters' => 'San Francisco, CA',
                'verified' => true,
                'subscription_plan' => 'premium',
                'benefits' => ['Health Insurance', 'Dental Insurance', 'Vision Insurance', '401k Matching', 'Unlimited PTO', 'Learning Budget'],
                'tech_stack' => ['Ruby on Rails', 'JavaScript', 'Python', 'Go', 'Kubernetes', 'AWS'],
                'contact_email' => 'careers@github.com',
                'website' => 'https://github.com',
                'remote_policy' => 'GitHub is a remote-first company. We believe that the best work happens when you have the flexibility to work from wherever you are most productive.',
            ],
            [
                'name' => 'Stripe',
                'slug' => 'stripe',
                'description' => 'Stripe is a technology company that builds economic infrastructure for the internet. Businesses of every size use our software to accept payments and manage their businesses online.',
                'industry' => 'Financial Technology',
                'size' => '500+',
                'headquarters' => 'San Francisco, CA',
                'verified' => true,
                'subscription_plan' => 'premium',
                'benefits' => ['Health Insurance', 'Dental Insurance', 'Vision Insurance', '401k Matching', 'Unlimited PTO', 'Home Office Stipend'],
                'tech_stack' => ['Ruby', 'JavaScript', 'Python', 'Go', 'React', 'PostgreSQL'],
                'contact_email' => 'jobs@stripe.com',
                'website' => 'https://stripe.com',
                'remote_policy' => 'Stripe supports remote work and has offices around the world. We believe in the power of distributed teams.',
            ],
            [
                'name' => 'Buffer',
                'slug' => 'buffer',
                'description' => 'Buffer is a social media management platform that helps small businesses grow their audience and reach more people.',
                'industry' => 'Social Media',
                'size' => '51-200',
                'headquarters' => 'San Francisco, CA',
                'verified' => true,
                'subscription_plan' => 'basic',
                'benefits' => ['Health Insurance', 'Dental Insurance', 'Unlimited PTO', 'Learning Budget', 'Home Office Stipend'],
                'tech_stack' => ['PHP', 'JavaScript', 'React', 'Node.js', 'MySQL', 'AWS'],
                'contact_email' => 'hello@buffer.com',
                'website' => 'https://buffer.com',
                'remote_policy' => 'Buffer is a fully remote company. We\'ve been remote-first since 2015 and believe in the power of distributed teams.',
            ],
            [
                'name' => 'Automattic',
                'slug' => 'automattic',
                'description' => 'Automattic is the company behind WordPress.com, WooCommerce, Jetpack, and other products that power the web.',
                'industry' => 'Web Development',
                'size' => '500+',
                'headquarters' => 'San Francisco, CA',
                'verified' => true,
                'subscription_plan' => 'premium',
                'benefits' => ['Health Insurance', 'Dental Insurance', 'Vision Insurance', '401k Matching', 'Unlimited PTO', 'Learning Budget', 'Home Office Stipend'],
                'tech_stack' => ['PHP', 'JavaScript', 'React', 'Node.js', 'MySQL', 'WordPress'],
                'contact_email' => 'jobs@automattic.com',
                'website' => 'https://automattic.com',
                'remote_policy' => 'Automattic is a distributed company with employees in over 50 countries. We believe in the power of remote work.',
            ],
            [
                'name' => 'Toggl',
                'slug' => 'toggl',
                'description' => 'Toggl is a time tracking tool that helps teams and individuals track their time and improve productivity.',
                'industry' => 'Productivity Software',
                'size' => '51-200',
                'headquarters' => 'Tallinn, Estonia',
                'verified' => true,
                'subscription_plan' => 'basic',
                'benefits' => ['Health Insurance', 'Dental Insurance', 'Unlimited PTO', 'Learning Budget', 'Home Office Stipend'],
                'tech_stack' => ['Ruby on Rails', 'JavaScript', 'React', 'PostgreSQL', 'Redis'],
                'contact_email' => 'jobs@toggl.com',
                'website' => 'https://toggl.com',
                'remote_policy' => 'Toggl is a remote-first company. We believe that great work can happen anywhere.',
            ],
        ];

        foreach ($companies as $companyData) {
            Company::create($companyData);
        }

        // Create sample jobs
        $jobs = [
            [
                'title' => 'Senior Full Stack Developer',
                'description' => 'We are looking for a Senior Full Stack Developer to join our team. You will be responsible for building and maintaining our web applications using modern technologies.',
                'requirements' => '• 5+ years of experience in full-stack development\n• Strong knowledge of JavaScript, React, and Node.js\n• Experience with databases (PostgreSQL, MongoDB)\n• Experience with cloud platforms (AWS, GCP)\n• Strong problem-solving skills\n• Excellent communication skills',
                'benefits' => '• Competitive salary\n• Health, dental, and vision insurance\n• 401k matching\n• Unlimited PTO\n• Learning and development budget\n• Home office stipend',
                'company_id' => 1, // GitHub
                'type' => 'full-time',
                'remote_type' => 'fully-remote',
                'salary_min' => 120000,
                'salary_max' => 180000,
                'salary_currency' => 'USD',
                'salary_period' => 'yearly',
                'tags' => ['JavaScript', 'React', 'Node.js', 'PostgreSQL', 'AWS', 'Full Stack'],
                'timezone' => 'Any',
                'visa_support' => true,
                'source' => 'manual',
                'apply_url' => 'https://github.com/careers',
                'featured' => true,
                'is_active' => true,
                'published_at' => now(),
                'expires_at' => now()->addDays(30),
            ],
            [
                'title' => 'Frontend Engineer',
                'description' => 'Join our frontend team and help build beautiful, responsive user interfaces. You will work with React, TypeScript, and modern CSS frameworks.',
                'requirements' => '• 3+ years of frontend development experience\n• Strong knowledge of React and TypeScript\n• Experience with CSS frameworks (Tailwind, Styled Components)\n• Experience with testing frameworks (Jest, Cypress)\n• Understanding of web performance optimization\n• Experience with Git and version control',
                'benefits' => '• Competitive salary\n• Health insurance\n• Dental and vision insurance\n• 401k matching\n• Flexible work hours\n• Remote work options',
                'company_id' => 2, // Stripe
                'type' => 'full-time',
                'remote_type' => 'fully-remote',
                'salary_min' => 100000,
                'salary_max' => 150000,
                'salary_currency' => 'USD',
                'salary_period' => 'yearly',
                'tags' => ['React', 'TypeScript', 'JavaScript', 'CSS', 'Frontend'],
                'timezone' => 'Any',
                'visa_support' => true,
                'source' => 'manual',
                'apply_url' => 'https://stripe.com/jobs',
                'featured' => false,
                'is_active' => true,
                'published_at' => now(),
                'expires_at' => now()->addDays(45),
            ],
            [
                'title' => 'Backend Developer',
                'description' => 'We are seeking a Backend Developer to join our engineering team. You will be responsible for building scalable APIs and microservices.',
                'requirements' => '• 4+ years of backend development experience\n• Strong knowledge of Python or Node.js\n• Experience with databases (PostgreSQL, Redis)\n• Experience with API design and development\n• Knowledge of cloud platforms (AWS, GCP)\n• Experience with Docker and Kubernetes',
                'benefits' => '• Competitive salary\n• Health insurance\n• Dental insurance\n• Vision insurance\n• 401k matching\n• Unlimited PTO\n• Learning budget',
                'company_id' => 3, // Buffer
                'type' => 'full-time',
                'remote_type' => 'fully-remote',
                'salary_min' => 90000,
                'salary_max' => 140000,
                'salary_currency' => 'USD',
                'salary_period' => 'yearly',
                'tags' => ['Python', 'Node.js', 'PostgreSQL', 'Redis', 'API Development', 'Backend'],
                'timezone' => 'Any',
                'visa_support' => false,
                'source' => 'manual',
                'apply_url' => 'https://buffer.com/jobs',
                'featured' => false,
                'is_active' => true,
                'published_at' => now(),
                'expires_at' => now()->addDays(60),
            ],
            [
                'title' => 'DevOps Engineer',
                'description' => 'Join our DevOps team and help us scale our infrastructure. You will work with AWS, Kubernetes, and modern CI/CD pipelines.',
                'requirements' => '• 3+ years of DevOps experience\n• Strong knowledge of AWS services\n• Experience with Kubernetes and Docker\n• Experience with CI/CD pipelines\n• Knowledge of monitoring and logging tools\n• Experience with Infrastructure as Code (Terraform)',
                'benefits' => '• Competitive salary\n• Health insurance\n• Dental insurance\n• Vision insurance\n• 401k matching\n• Unlimited PTO\n• Home office stipend',
                'company_id' => 4, // Automattic
                'type' => 'full-time',
                'remote_type' => 'fully-remote',
                'salary_min' => 110000,
                'salary_max' => 160000,
                'salary_currency' => 'USD',
                'salary_period' => 'yearly',
                'tags' => ['AWS', 'Kubernetes', 'Docker', 'CI/CD', 'Terraform', 'DevOps'],
                'timezone' => 'Any',
                'visa_support' => true,
                'source' => 'manual',
                'apply_url' => 'https://automattic.com/work-with-us',
                'featured' => true,
                'is_active' => true,
                'published_at' => now(),
                'expires_at' => now()->addDays(30),
            ],
            [
                'title' => 'Product Manager',
                'description' => 'We are looking for a Product Manager to join our team and help drive product strategy and roadmap. You will work closely with engineering and design teams.',
                'requirements' => '• 3+ years of product management experience\n• Experience with agile development methodologies\n• Strong analytical and problem-solving skills\n• Experience with user research and data analysis\n• Excellent communication and leadership skills\n• Technical background preferred',
                'benefits' => '• Competitive salary\n• Health insurance\n• Dental insurance\n• Vision insurance\n• 401k matching\n• Unlimited PTO\n• Learning budget',
                'company_id' => 5, // Toggl
                'type' => 'full-time',
                'remote_type' => 'fully-remote',
                'salary_min' => 80000,
                'salary_max' => 120000,
                'salary_currency' => 'USD',
                'salary_period' => 'yearly',
                'tags' => ['Product Management', 'Agile', 'User Research', 'Analytics', 'Strategy'],
                'timezone' => 'Any',
                'visa_support' => false,
                'source' => 'manual',
                'apply_url' => 'https://toggl.com/jobs',
                'featured' => false,
                'is_active' => true,
                'published_at' => now(),
                'expires_at' => now()->addDays(45),
            ],
            [
                'title' => 'UX Designer',
                'description' => 'Join our design team and help create amazing user experiences. You will work on user research, wireframing, prototyping, and visual design.',
                'requirements' => '• 3+ years of UX design experience\n• Strong portfolio showcasing UX design skills\n• Experience with design tools (Figma, Sketch, Adobe Creative Suite)\n• Experience with user research and usability testing\n• Understanding of design systems\n• Experience with prototyping tools',
                'benefits' => '• Competitive salary\n• Health insurance\n• Dental insurance\n• Vision insurance\n• 401k matching\n• Unlimited PTO\n• Learning budget\n• Home office stipend',
                'company_id' => 1, // GitHub
                'type' => 'full-time',
                'remote_type' => 'fully-remote',
                'salary_min' => 85000,
                'salary_max' => 130000,
                'salary_currency' => 'USD',
                'salary_period' => 'yearly',
                'tags' => ['UX Design', 'UI Design', 'Figma', 'User Research', 'Prototyping', 'Design Systems'],
                'timezone' => 'Any',
                'visa_support' => true,
                'source' => 'manual',
                'apply_url' => 'https://github.com/careers',
                'featured' => false,
                'is_active' => true,
                'published_at' => now(),
                'expires_at' => now()->addDays(60),
            ],
        ];

        foreach ($jobs as $jobData) {
            Job::create($jobData);
        }
    }
}
