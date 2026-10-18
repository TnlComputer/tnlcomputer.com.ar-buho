<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Skill;
use Illuminate\Database\Seeder;

class PortfolioSeeder extends Seeder
{
    public function run(): void
    {
        // "Stack principal" es lo que muestra el portfolio publicado; el resto viene del CV anterior.
        $skills = [
            'Stack principal' => ['Laravel / PHP', 'Java / Spring', 'Python', 'JavaScript', 'MySQL / SQL', 'APIs REST', 'BI / Datos', 'IA aplicada'],
            'Frontend' => ['HTML', 'CSS', 'React', 'Vue', 'TypeScript', 'Angular'],
            'Herramientas' => ['Git / GitHub', 'Docker', 'Bash', 'Symfony', 'Django', 'Node', 'Oracle Cloud', 'Solidity', 'COBOL'],
            'Diseño y multimedia' => ['Figma', 'Illustrator', 'Photoshop', 'Premiere', 'After Effects', 'Camtasia'],
        ];

        foreach ($skills as $group => $items) {
            foreach ($items as $sort => $name) {
                Skill::updateOrCreate(['name' => $name, 'group' => $group], ['sort' => $sort]);
            }
        }

        $gh = 'https://github.com/TnlComputer/';
        $img = 'img/portfolio/proyectos/';

        // Selección revisando los 56 repos públicos (octubre 2026). Los de clientes describen qué hace el sistema
        // sin datos internos. Todo se edita desde /admin/proyectos.
        $projects = [
            [
                'title' => 'Fraschina — Sistema de gestión', 'title_en' => 'Fraschina — Management system',
                'description' => 'Sistema de gestión para una distribuidora de harinas que reemplazó planillas Excel y un sistema PHP anterior: clientes, pedidos con IVA, planillas de reparto, central de pedidos a molinos con revisiones, control de entregas e informes. Incluye permisos por rol, auditoría completa, PDF/Excel, envíos por mail y tareas programadas.',
                'description_en' => 'Management system for a flour distributor that replaced Excel sheets and a legacy PHP app: customers, orders with VAT, delivery sheets, a mill order hub with revisions, delivery tracking and reports. Includes role-based permissions, full audit trail, PDF/Excel export, email dispatch and scheduled jobs.',
                'kind' => 'Sistema web', 'kind_en' => 'Web system', 'tags' => ['Laravel 11', 'PHP', 'MySQL', 'AdminLTE', 'Spatie Permission', 'DomPDF'],
                'image' => null, 'demo_url' => null, 'code_url' => $gh.'Fraschina-2024', 'year' => 2026, 'featured' => true,
            ],
            [
                'title' => 'EnergIA — Hackathon ONE G9', 'title_en' => 'EnergIA — ONE G9 Hackathon',
                'description' => 'Plataforma del Team 48 para analizar el consumo eléctrico: dashboard, historial de consumos, análisis con un modelo de Machine Learning y recomendaciones de ahorro. Incluye login con JWT y Google, verificación por email, panel de administración e interfaz multilenguaje con tema claro/oscuro.',
                'description_en' => 'Team 48 platform for analyzing electricity consumption: dashboard, consumption history, Machine Learning–based analysis and saving recommendations. Includes JWT and Google login, email verification, an admin panel and a multilingual UI with light/dark theme.',
                'kind' => 'Hackathon', 'kind_en' => 'Hackathon', 'tags' => ['React', 'Spring Boot', 'JWT', 'MySQL', 'FastAPI', 'Machine Learning'],
                'image' => $img.'web-energia.png', 'demo_url' => null,
                'code_url' => 'https://github.com/No-Country-simulation/G9-LATAM-Team-48', 'year' => 2026, 'featured' => true,
            ],
            [
                'title' => 'Alura Foro — API REST + Frontend', 'title_en' => 'Alura Forum — REST API + Frontend',
                'description' => 'Foro con API REST en Spring Boot y autenticación stateless con JWT: tópicos, respuestas anidadas, solución aceptada y permisos por rol y autoría. El frontend en JavaScript consume la API e incluye panel de administración de usuarios y tópicos y modo oscuro.',
                'description_en' => 'Forum backed by a Spring Boot REST API with stateless JWT authentication: topics, nested replies, accepted answers and role/authorship-based permissions. The JavaScript frontend consumes the API and includes a user/topic admin panel and dark mode.',
                'kind' => 'API', 'kind_en' => 'API', 'tags' => ['Java 21', 'Spring Boot 3', 'Spring Security', 'JWT', 'MySQL', 'Flyway', 'Swagger'],
                'image' => $img.'gh-alura_latam_-g9-challenge_java-spring-api-blog.png', 'demo_url' => null,
                'code_url' => $gh.'Alura_Latam_-G9-Challenge_Java-Spring-Api-Blog', 'year' => 2025, 'featured' => true,
            ],
            [
                'title' => 'El.Anzuelo — Tienda online', 'title_en' => 'El.Anzuelo — Online store',
                'description' => 'E-commerce académico de artículos de pesca en React: catálogo por rubros, buscador, favoritos, carrito y checkout simulado que valida y descuenta stock en una transacción del lado del servidor. Tiene panel de administración con login para productos, rubros, galería de imágenes, zonas de envío y anuncios.',
                'description_en' => 'Academic fishing-gear e-commerce built with React: catalog by category, search, favorites, cart and a simulated checkout that validates and decrements stock in a server-side transaction. Includes a login-protected admin panel for products, categories, image gallery, shipping zones and announcements.',
                'kind' => 'Web app', 'kind_en' => 'Web app', 'tags' => ['React 19', 'Vite', 'Firebase', 'Firestore', 'Netlify Functions'],
                'image' => $img.'web-elanzuelo.png', 'demo_url' => 'https://react-final-eccomerce.netlify.app/',
                'code_url' => $gh.'entrega-final-react', 'year' => 2026, 'featured' => true,
            ],
            [
                'title' => 'Soria Performance', 'title_en' => 'Soria Performance',
                'description' => 'Sitio y plataforma de un taller de preparación y chiptuning: servicios, novedades y resultados en banco de pruebas, más venta de cursos online con pago por MercadoPago o PayPal. Los usuarios acceden a los videos de sus cursos y hay un dashboard para administrar cursos, temarios, ofertas, pagos y usuarios.',
                'description_en' => 'Website and platform for a performance tuning shop: services, news and dyno results, plus online course sales paid via MercadoPago or PayPal. Users access their course videos, and an admin dashboard manages courses, syllabi, offers, payments and users.',
                'kind' => 'Sitio web', 'kind_en' => 'Website', 'tags' => ['Laravel 10', 'Livewire', 'MySQL', 'MercadoPago', 'PayPal'],
                'image' => $img.'web-soria.png', 'demo_url' => 'https://aldosoria.com.ar', 'code_url' => $gh.'aldosoria.com.ar---hosting', 'year' => 2025, 'featured' => false,
            ],
            [
                'title' => 'SymonSport — Gestión de lubricentro', 'title_en' => 'SymonSport — Auto service shop system',
                'description' => 'Sistema web para un lubricentro: los clientes registran sus vehículos, reservan turnos y hacen pedidos, y el taller registra los servicios y trabajos realizados. Tiene panel de administración para usuarios, tipos de turno, servicios y productos con precios y stock.',
                'description_en' => 'Web system for an auto service shop: customers register their vehicles, book appointments and place orders, while the shop logs services and work performed. Includes an admin panel for users, appointment types, services and products with prices and stock.',
                'kind' => 'Sistema web', 'kind_en' => 'Web system', 'tags' => ['Laravel 12', 'Breeze', 'MySQL', 'Blade'],
                'image' => null, 'demo_url' => null, 'code_url' => $gh.'SymonSport-Lubricentro', 'year' => 2025, 'featured' => false,
            ],
            [
                'title' => 'Voll Med API', 'title_en' => 'Voll Med API',
                'description' => 'API REST para gestionar médicos, pacientes y consultas médicas, con paginación, baja lógica y DTOs. Las reglas de negocio para reservar y cancelar consultas se resuelven con validadores que se inyectan automáticamente, y la API se documenta con Swagger.',
                'description_en' => 'REST API for managing doctors, patients and medical appointments, with pagination, soft deletes and DTOs. Booking and cancellation business rules are handled by auto-injected validators, and the API is documented with Swagger.',
                'kind' => 'API', 'kind_en' => 'API', 'tags' => ['Java', 'Spring Boot 3', 'Spring Data JPA', 'MySQL', 'Swagger', 'Docker'],
                'image' => null, 'demo_url' => null, 'code_url' => $gh.'Alura-One-G9-API-REST-VOLLMED_API', 'year' => 2025, 'featured' => false,
            ],
            [
                'title' => 'Screenmatch', 'title_en' => 'Screenmatch',
                'description' => 'Proyecto full stack con backend en Spring Boot que expone endpoints REST de películas y series y un frontend en JavaScript que los consume y muestra catálogo y detalle. Incluye un endpoint opcional de análisis con la API de OpenAI.',
                'description_en' => 'Full-stack project with a Spring Boot backend exposing REST endpoints for movies and series, and a JavaScript frontend that consumes them to show a catalog and detail views. Includes an optional analysis endpoint powered by the OpenAI API.',
                'kind' => 'Web app', 'kind_en' => 'Web app', 'tags' => ['Java 17', 'Spring Boot', 'REST', 'JavaScript', 'OpenAI'],
                'image' => $img.'gh-screenmatch-spring.png', 'demo_url' => null, 'code_url' => $gh.'screenmatch-spring', 'year' => 2025, 'featured' => false,
            ],
            [
                'title' => 'Taxis Barzola', 'title_en' => 'Taxis Barzola',
                'description' => 'Sitio para una agencia de autos: catálogo de 0 km y usados con sus características, administrado desde una base MySQL.',
                'description_en' => 'Website for a car dealer: catalog of new and used cars with their specs, managed from a MySQL database.',
                'kind' => 'Sitio web', 'kind_en' => 'Website', 'tags' => ['PHP', 'MySQL', 'JavaScript'],
                'image' => $img.'web-taxisbarzola.png', 'demo_url' => 'https://www.taxisbarzola.com.ar', 'code_url' => null, 'year' => 2020, 'featured' => false,
            ],
        ];

        foreach ($projects as $i => $project) {
            Project::updateOrCreate(['title' => $project['title']], $project + ['sort' => $i, 'active' => true]);
        }
    }
}
