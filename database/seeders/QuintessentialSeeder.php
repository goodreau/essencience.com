<?php

namespace Database\Seeders;

use App\Models\Quintessential;
use Illuminate\Database\Seeder;

class QuintessentialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quintessentials = [
            [
                'number' => 1,
                'name' => 'Truth',
                'slug' => 'truth',
                'description' => 'The foundation of authentic existence and honest self-expression.',
                'content' => 'Truth represents the bedrock of the Quintessential path—the unwavering commitment to authentic existence, honest self-expression, and alignment with reality as it is, not as we wish it to be.',
                'icon' => 'θ',
                'color' => '#FF5600',
                'order_by' => 1,
            ],
            [
                'number' => 2,
                'name' => 'Justice',
                'slug' => 'justice',
                'description' => 'Fairness, equity, and the balance of rights and responsibilities.',
                'content' => 'Justice is the principle of fairness and moral rightness, ensuring that all beings receive what is due to them in accordance with their nature and actions.',
                'icon' => '⚖',
                'color' => '#F45800',
                'order_by' => 2,
            ],
            [
                'number' => 3,
                'name' => 'Beauty',
                'slug' => 'beauty',
                'description' => 'The aesthetic harmony that elevates the human spirit.',
                'content' => 'Beauty is the manifestation of harmony, proportion, and elegance that touches the soul and elevates human consciousness beyond the mundane.',
                'icon' => '✦',
                'color' => '#F6405F',
                'order_by' => 3,
            ],
            [
                'number' => 4,
                'name' => 'Love',
                'slug' => 'love',
                'description' => 'The binding force of compassion, connection, and care.',
                'content' => 'Love is the quintessential force that binds all existence—compassion, connection, and the recognition of the divine spark in all beings.',
                'icon' => '♥',
                'color' => '#FF6B9D',
                'order_by' => 4,
            ],
            [
                'number' => 5,
                'name' => 'Balance',
                'slug' => 'balance',
                'description' => 'Equilibrium between opposing forces and harmonious proportion.',
                'content' => 'Balance represents the dynamic equilibrium between opposing forces—the middle path that honors both stability and growth, tradition and innovation.',
                'icon' => '⚖',
                'color' => '#5A5A5A',
                'order_by' => 5,
            ],
            [
                'number' => 6,
                'name' => 'Reflection',
                'slug' => 'reflection',
                'description' => 'The practice of introspection, self-awareness, and conscious examination.',
                'content' => 'Reflection is the contemplative practice of turning awareness inward, examining one\'s thoughts, actions, and patterns to gain wisdom and self-knowledge.',
                'icon' => '🪞',
                'color' => '#4A90E2',
                'order_by' => 6,
            ],
            [
                'number' => 7,
                'name' => 'Harmonic',
                'slug' => 'harmonic',
                'description' => 'The resonant frequencies of existence working in concert.',
                'content' => 'Harmonic represents the principle of resonance and alignment—when individual elements vibrate in sympathetic frequencies, creating greater coherence and power.',
                'icon' => '♫',
                'color' => '#9B59B6',
                'order_by' => 7,
            ],
            [
                'number' => 8,
                'name' => 'Integration',
                'slug' => 'integration',
                'description' => 'The synthesis of disparate parts into a coherent whole.',
                'content' => 'Integration is the process of bringing together seemingly separate elements—ideas, experiences, systems—into a unified, coherent whole that is greater than the sum of its parts.',
                'icon' => '⚛',
                'color' => '#2ECC71',
                'order_by' => 8,
            ],
            [
                'number' => 9,
                'name' => 'Transformation',
                'slug' => 'transformation',
                'description' => 'The alchemical process of fundamental change and evolution.',
                'content' => 'Transformation is the profound metamorphosis from one state of being to another—the death of the old self and the birth of the new, guided by conscious intention.',
                'icon' => '⟲',
                'color' => '#E74C3C',
                'order_by' => 9,
            ],
            [
                'number' => 10,
                'name' => 'Unification',
                'slug' => 'unification',
                'description' => 'The ultimate convergence into singular essence and purpose.',
                'content' => 'Unification represents the culmination of the Quintessential path—the recognition of fundamental oneness underlying all apparent diversity, the return to source.',
                'icon' => '◉',
                'color' => '#F39C12',
                'order_by' => 10,
            ],
        ];

        foreach ($quintessentials as $quintessential) {
            Quintessential::create($quintessential);
        }
    }
}
