<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Course::updateOrCreate([
            'code' => 'BSIT',
        ], [
            'name' => 'Bachelor of Science in Information Technology',
            'description' => 'A comprehensive program focusing on software development, database management, networking, and IT systems. Students gain practical skills in programming, web development, cybersecurity, and emerging technologies to meet the demands of the modern digital workplace.',
            'duration' => '4 years',
            'ideal_score_range' => '80-100',
            'subjects' => [
                'Programming Languages',
                'Database Management',
                'Web Development',
                'Software Engineering',
                'Cybersecurity',
                'Cloud Computing',
                'Data Structures',
                'Computer Networks'
            ],
        ]);

        Course::updateOrCreate([
            'code' => 'BSED-SCI',
        ], [
            'name' => 'Bachelor of Secondary Education Major in Science',
            'description' => 'A teacher education program that prepares future educators to teach in secondary schools. The program combines theoretical knowledge with hands-on teaching practice to develop competent and reflective teachers.',
            'duration' => '4 years',
            'ideal_score_range' => '75-95',
            'subjects' => [
                'Child Development',
                'Educational Psychology',
                'Curriculum Design',
                'Teaching Methods',
                'Assessment and Evaluation',
                'Classroom Management',
                'Subject Specialization',
                'Practice Teaching'
            ],
        ]);

        Course::updateOrCreate([
            'code' => 'BSBA-HRM',
        ], [
            'name' => 'Bachelor of Science in Business Administration Major in Human Resource Management',
            'description' => 'A business-focused program developing leadership, management, and entrepreneurial skills. The curriculum covers core business functions including marketing, finance, operations, and human resource management to prepare students for diverse careers in the corporate world.',
            'duration' => '4 years',
            'ideal_score_range' => '70-90',
            'subjects' => [
                'Business Management',
                'Accounting',
                'Marketing',
                'Finance',
                'Human Resource Management',
                'Business Law',
                'Entrepreneurship',
                'Strategic Management'
            ],
        ]);
    }
}
