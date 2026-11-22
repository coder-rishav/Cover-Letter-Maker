<?php

declare(strict_types=1);

namespace CoverLetterMaker;

/**
 * A local, curated dictionary of resume skills with matching aliases.
 *
 * Matching is boundary-aware: short or all-caps aliases (C++, AWS, REST, Go)
 * are matched case-sensitively so ordinary prose does not create false hits.
 */
final class SkillsDictionary
{
    public const CATEGORY_LABELS = [
        'languages' => 'Languages',
        'frameworks' => 'Frameworks & Libraries',
        'databases' => 'Databases & Messaging',
        'cloud_devops' => 'Cloud & DevOps',
        'tools' => 'Tools & Platforms',
        'data_ai' => 'Data & AI',
        'practices' => 'Practices & Methods',
        'soft_skills' => 'Soft Skills',
    ];

    /**
     * @var array<string, array<string, list<string>>>
     */
    private const SKILLS = [
        'languages' => [
            'PHP' => ['PHP'],
            'JavaScript' => ['JavaScript', 'ECMAScript'],
            'TypeScript' => ['TypeScript'],
            'Python' => ['Python'],
            'Java' => ['Java'],
            'C' => ['C programming', 'ANSI C', 'C language'],
            'C++' => ['C++'],
            'C#' => ['C#'],
            'Go' => ['Go', 'Golang'],
            'Rust' => ['Rust'],
            'Ruby' => ['Ruby'],
            'Swift' => ['Swift'],
            'Kotlin' => ['Kotlin'],
            'Scala' => ['Scala'],
            'Dart' => ['Dart'],
            'Elixir' => ['Elixir'],
            'Erlang' => ['Erlang'],
            'Haskell' => ['Haskell'],
            'Clojure' => ['Clojure'],
            'R' => ['R programming', 'R language', 'RStudio'],
            'MATLAB' => ['MATLAB'],
            'Perl' => ['Perl'],
            'Lua' => ['Lua'],
            'Objective-C' => ['Objective-C', 'Objective C'],
            'Visual Basic' => ['Visual Basic', 'VB.NET'],
            'Assembly' => ['Assembly', 'Assembly language'],
            'Bash' => ['Bash', 'Shell scripting'],
            'PowerShell' => ['PowerShell'],
            'SQL' => ['SQL'],
            'HTML' => ['HTML'],
            'HTML5' => ['HTML5'],
            'CSS' => ['CSS'],
            'CSS3' => ['CSS3'],
            'Sass' => ['Sass', 'SCSS'],
            'Less' => ['Less.js'],
            'GraphQL' => ['GraphQL'],
            'Solidity' => ['Solidity'],
            'COBOL' => ['COBOL'],
            'Fortran' => ['Fortran'],
            'Groovy' => ['Groovy'],
            'F#' => ['F#'],
            'Julia' => ['Julia'],
            'Scheme' => ['Scheme'],
            'Prolog' => ['Prolog'],
            'Apex' => ['Apex'],
            'VBA' => ['VBA'],
        ],
        'frameworks' => [
            'Laravel' => ['Laravel'],
            'Symfony' => ['Symfony'],
            'CodeIgniter' => ['CodeIgniter'],
            'Slim' => ['Slim Framework'],
            'Yii' => ['Yii'],
            'CakePHP' => ['CakePHP'],
            'React' => ['React', 'React.js', 'ReactJS'],
            'Angular' => ['Angular'],
            'AngularJS' => ['AngularJS', 'Angular.js'],
            'Vue.js' => ['Vue.js', 'VueJS', 'Vue 3', 'Vue'],
            'Svelte' => ['Svelte'],
            'SvelteKit' => ['SvelteKit'],
            'Next.js' => ['Next.js', 'NextJS'],
            'Nuxt.js' => ['Nuxt.js', 'NuxtJS', 'Nuxt'],
            'Node.js' => ['Node.js', 'NodeJS', 'Node'],
            'Express.js' => ['Express.js', 'ExpressJS', 'Express'],
            'NestJS' => ['NestJS'],
            'Django' => ['Django'],
            'Flask' => ['Flask'],
            'FastAPI' => ['FastAPI'],
            'Pyramid' => ['Pyramid Framework'],
            'Spring Framework' => ['Spring Framework', 'Spring MVC'],
            'Spring Boot' => ['Spring Boot'],
            'Hibernate' => ['Hibernate'],
            'ASP.NET' => ['ASP.NET'],
            '.NET' => ['.NET', 'dotnet', '.NET Core'],
            'Entity Framework' => ['Entity Framework'],
            'Livewire' => ['Livewire'],
            'Bootstrap' => ['Bootstrap'],
            'Tailwind CSS' => ['Tailwind CSS', 'TailwindCSS', 'Tailwind'],
            'Material UI' => ['Material UI', 'Material-UI', 'MUI'],
            'jQuery' => ['jQuery'],
            'Alpine.js' => ['Alpine.js', 'AlpineJS'],
            'Electron' => ['Electron'],
            'Ionic' => ['Ionic'],
            'Flutter' => ['Flutter'],
            'React Native' => ['React Native'],
            'Ruby on Rails' => ['Ruby on Rails', 'Rails'],
            'Sinatra' => ['Sinatra'],
            'Phoenix' => ['Phoenix Framework'],
            'Unity' => ['Unity', 'Unity3D'],
            'Unreal Engine' => ['Unreal Engine', 'Unreal'],
            'Godot' => ['Godot'],
            'Qt' => ['Qt'],
            'WordPress' => ['WordPress'],
            'Drupal' => ['Drupal'],
            'Joomla' => ['Joomla'],
            'Magento' => ['Magento'],
            'Shopify' => ['Shopify'],
            'WooCommerce' => ['WooCommerce'],
            'Astro' => ['Astro'],
            'Remix' => ['Remix'],
            'Gatsby' => ['Gatsby'],
            'Strapi' => ['Strapi'],
            'Sanity' => ['Sanity'],
            'Fastify' => ['Fastify'],
            'Koa' => ['Koa'],
            'AdonisJS' => ['AdonisJS'],
            'Phoenix LiveView' => ['Phoenix LiveView', 'LiveView'],
            'TYPO3' => ['TYPO3'],
        ],
        'databases' => [
            'MySQL' => ['MySQL'],
            'PostgreSQL' => ['PostgreSQL', 'Postgres'],
            'SQLite' => ['SQLite'],
            'MariaDB' => ['MariaDB'],
            'Oracle Database' => ['Oracle Database', 'Oracle DB'],
            'Microsoft SQL Server' => ['Microsoft SQL Server', 'SQL Server', 'MSSQL'],
            'MongoDB' => ['MongoDB', 'Mongo'],
            'Redis' => ['Redis'],
            'Cassandra' => ['Cassandra'],
            'CouchDB' => ['CouchDB'],
            'DynamoDB' => ['DynamoDB'],
            'Firebase' => ['Firebase', 'Firestore'],
            'Neo4j' => ['Neo4j'],
            'Elasticsearch' => ['Elasticsearch'],
            'OpenSearch' => ['OpenSearch'],
            'ClickHouse' => ['ClickHouse'],
            'Snowflake' => ['Snowflake'],
            'BigQuery' => ['BigQuery'],
            'Redshift' => ['Redshift'],
            'CockroachDB' => ['CockroachDB'],
            'Memcached' => ['Memcached'],
            'RabbitMQ' => ['RabbitMQ'],
            'Apache Kafka' => ['Apache Kafka', 'Kafka'],
            'Supabase' => ['Supabase'],
            'PlanetScale' => ['PlanetScale'],
            'FaunaDB' => ['FaunaDB'],
            'InfluxDB' => ['InfluxDB'],
            'TimescaleDB' => ['TimescaleDB'],
            'Oracle' => ['Oracle'],
            'Prisma' => ['Prisma'],
            'Doctrine' => ['Doctrine ORM'],
        ],
        'cloud_devops' => [
            'AWS' => ['AWS', 'Amazon Web Services'],
            'Azure' => ['Azure', 'Microsoft Azure'],
            'Google Cloud Platform' => ['Google Cloud Platform', 'GCP', 'Google Cloud'],
            'DigitalOcean' => ['DigitalOcean'],
            'Heroku' => ['Heroku'],
            'Vercel' => ['Vercel'],
            'Netlify' => ['Netlify'],
            'Cloudflare' => ['Cloudflare'],
            'Kubernetes' => ['Kubernetes', 'K8s'],
            'Docker' => ['Docker'],
            'Docker Compose' => ['Docker Compose'],
            'Podman' => ['Podman'],
            'Terraform' => ['Terraform'],
            'Ansible' => ['Ansible'],
            'Puppet' => ['Puppet'],
            'Chef' => ['Chef Infra', 'Chef'],
            'Jenkins' => ['Jenkins'],
            'GitHub Actions' => ['GitHub Actions'],
            'GitLab CI' => ['GitLab CI', 'GitLab CI/CD'],
            'CircleCI' => ['CircleCI'],
            'Travis CI' => ['Travis CI'],
            'Argo CD' => ['Argo CD', 'ArgoCD'],
            'Helm' => ['Helm'],
            'Prometheus' => ['Prometheus'],
            'Grafana' => ['Grafana'],
            'Datadog' => ['Datadog'],
            'Sentry' => ['Sentry'],
            'New Relic' => ['New Relic'],
            'ELK Stack' => ['ELK Stack', 'Elastic Stack'],
            'Nginx' => ['Nginx'],
            'Apache' => ['Apache HTTP Server', 'Apache HTTPD'],
            'Linux' => ['Linux'],
            'Ubuntu' => ['Ubuntu'],
            'CentOS' => ['CentOS'],
            'AWS Lambda' => ['AWS Lambda', 'Lambda functions'],
            'Amazon S3' => ['Amazon S3', 'S3'],
            'Amazon EC2' => ['Amazon EC2', 'EC2'],
            'AWS RDS' => ['AWS RDS', 'RDS'],
            'CloudFront' => ['CloudFront'],
            'OpenShift' => ['OpenShift'],
            'Vagrant' => ['Vagrant'],
            'Packer' => ['Packer'],
            'Istio' => ['Istio'],
            'Consul' => ['Consul'],
            'HashiCorp Vault' => ['HashiCorp Vault', 'Vault'],
            'Serverless Framework' => ['Serverless Framework'],
            'CloudFormation' => ['CloudFormation'],
            'Azure DevOps' => ['Azure DevOps'],
            'Google Kubernetes Engine' => ['Google Kubernetes Engine', 'GKE'],
            'Route 53' => ['Route 53'],
            'Cloudflare Workers' => ['Cloudflare Workers'],
            'Fly.io' => ['Fly.io'],
            'Render' => ['Render'],
            'Railway' => ['Railway'],
            'Pulumi' => ['Pulumi'],
            'Rancher' => ['Rancher'],
            'AWS CloudWatch' => ['AWS CloudWatch', 'CloudWatch'],
            'GitHub Packages' => ['GitHub Packages'],
        ],
        'tools' => [
            'Git' => ['Git'],
            'GitHub' => ['GitHub'],
            'GitLab' => ['GitLab'],
            'Bitbucket' => ['Bitbucket'],
            'Jira' => ['Jira'],
            'Confluence' => ['Confluence'],
            'Trello' => ['Trello'],
            'Asana' => ['Asana'],
            'Notion' => ['Notion'],
            'Slack' => ['Slack'],
            'Figma' => ['Figma'],
            'Adobe XD' => ['Adobe XD'],
            'Sketch' => ['Sketch'],
            'Photoshop' => ['Photoshop', 'Adobe Photoshop'],
            'Illustrator' => ['Illustrator', 'Adobe Illustrator'],
            'Postman' => ['Postman'],
            'Insomnia' => ['Insomnia'],
            'Swagger' => ['Swagger'],
            'OpenAPI' => ['OpenAPI'],
            'PHPUnit' => ['PHPUnit'],
            'Pest' => ['Pest'],
            'Jest' => ['Jest'],
            'Mocha' => ['Mocha'],
            'Cypress' => ['Cypress'],
            'Selenium' => ['Selenium'],
            'Playwright' => ['Playwright'],
            'PHPStan' => ['PHPStan'],
            'Psalm' => ['Psalm'],
            'ESLint' => ['ESLint'],
            'Prettier' => ['Prettier'],
            'Webpack' => ['Webpack'],
            'Vite' => ['Vite'],
            'Babel' => ['Babel'],
            'Composer' => ['Composer'],
            'npm' => ['npm'],
            'Yarn' => ['Yarn'],
            'pnpm' => ['pnpm'],
            'Gradle' => ['Gradle'],
            'Maven' => ['Maven'],
            'Visual Studio Code' => ['Visual Studio Code', 'VS Code'],
            'PhpStorm' => ['PhpStorm'],
            'IntelliJ IDEA' => ['IntelliJ IDEA', 'IntelliJ'],
            'Xdebug' => ['Xdebug'],
            'GNU Make' => ['GNU Make', 'Makefile'],
            'Apache JMeter' => ['Apache JMeter', 'JMeter'],
            'k6' => ['k6'],
            'SonarQube' => ['SonarQube'],
            'Snyk' => ['Snyk'],
            'Dependabot' => ['Dependabot'],
            'Rollup' => ['Rollup'],
            'Storybook' => ['Storybook'],
            'Chrome DevTools' => ['Chrome DevTools', 'DevTools'],
            'LaTeX' => ['LaTeX'],
            'Microsoft Excel' => ['Microsoft Excel', 'MS Excel', 'Excel'],
            'Google Analytics' => ['Google Analytics'],
            'Google Tag Manager' => ['Google Tag Manager'],
            'HubSpot' => ['HubSpot'],
            'Salesforce' => ['Salesforce'],
            'Zendesk' => ['Zendesk'],
            'Intercom' => ['Intercom'],
            'Twilio' => ['Twilio'],
            'Stripe' => ['Stripe'],
        ],
        'data_ai' => [
            'Machine Learning' => ['Machine Learning', 'ML models'],
            'Deep Learning' => ['Deep Learning'],
            'Natural Language Processing' => ['Natural Language Processing', 'NLP'],
            'Computer Vision' => ['Computer Vision'],
            'Data Analysis' => ['Data Analysis', 'Data Analytics'],
            'Data Visualization' => ['Data Visualization'],
            'ETL' => ['ETL', 'Extract Transform Load'],
            'Data Warehousing' => ['Data Warehousing', 'Data Warehouse'],
            'Big Data' => ['Big Data'],
            'Statistics' => ['Statistics', 'Statistical Analysis'],
            'A/B Testing' => ['A/B Testing', 'AB Testing'],
            'Tableau' => ['Tableau'],
            'Power BI' => ['Power BI', 'PowerBI'],
            'Looker' => ['Looker'],
            'Apache Spark' => ['Apache Spark', 'Spark'],
            'Apache Hadoop' => ['Apache Hadoop', 'Hadoop'],
            'Apache Airflow' => ['Apache Airflow', 'Airflow'],
            'dbt' => ['dbt'],
            'PyTorch' => ['PyTorch'],
            'TensorFlow' => ['TensorFlow'],
            'Keras' => ['Keras'],
            'Scikit-learn' => ['Scikit-learn', 'scikit learn'],
            'Pandas' => ['Pandas'],
            'NumPy' => ['NumPy'],
            'OpenCV' => ['OpenCV'],
            'Hugging Face' => ['Hugging Face', 'HuggingFace'],
            'LangChain' => ['LangChain'],
            'MLflow' => ['MLflow'],
            'Kubeflow' => ['Kubeflow'],
            'Jupyter' => ['Jupyter', 'Jupyter Notebook'],
            'Databricks' => ['Databricks'],
            'Snowpark' => ['Snowpark'],
            'Apache Flink' => ['Apache Flink', 'Flink'],
            'Apache Beam' => ['Apache Beam'],
            'Prophet' => ['Prophet'],
            'XGBoost' => ['XGBoost'],
            'LightGBM' => ['LightGBM'],
            'Plotly' => ['Plotly'],
            'Matplotlib' => ['Matplotlib'],
            'Seaborn' => ['Seaborn'],
            'Altair' => ['Altair'],
            'Data Modeling' => ['Data Modeling', 'Data Modelling'],
        ],
        'practices' => [
            'Agile' => ['Agile'],
            'Scrum' => ['Scrum'],
            'Kanban' => ['Kanban'],
            'Waterfall' => ['Waterfall'],
            'Test-Driven Development' => ['Test-Driven Development', 'TDD'],
            'Behavior-Driven Development' => ['Behavior-Driven Development', 'BDD'],
            'CI/CD' => ['CI/CD', 'CICD', 'Continuous Integration'],
            'DevOps' => ['DevOps'],
            'Object-Oriented Programming' => ['Object-Oriented Programming', 'OOP'],
            'SOLID' => ['SOLID principles', 'SOLID'],
            'MVC' => ['MVC'],
            'REST APIs' => ['REST APIs', 'REST API', 'RESTful APIs', 'RESTful'],
            'Microservices' => ['Microservices', 'Microservice architecture'],
            'Serverless' => ['Serverless'],
            'Pair Programming' => ['Pair Programming'],
            'Code Review' => ['Code Review', 'Code Reviews'],
            'Unit Testing' => ['Unit Testing'],
            'Integration Testing' => ['Integration Testing'],
            'Performance Optimization' => ['Performance Optimization', 'Performance Tuning'],
            'System Design' => ['System Design'],
            'Technical Documentation' => ['Technical Documentation'],
            'API Design' => ['API Design'],
            'Database Design' => ['Database Design'],
            'Scalability' => ['Scalability', 'Scalable systems'],
            'Refactoring' => ['Refactoring'],
            'Design Patterns' => ['Design Patterns'],
            'Domain-Driven Design' => ['Domain-Driven Design', 'DDD'],
            'Clean Architecture' => ['Clean Architecture'],
            'Event-Driven Architecture' => ['Event-Driven Architecture'],
            'Load Testing' => ['Load Testing'],
            'Security Auditing' => ['Security Auditing', 'Security Audit'],
            'Accessibility' => ['Accessibility', 'WCAG', 'a11y'],
            'Responsive Design' => ['Responsive Design'],
            'Mobile-First Design' => ['Mobile-First Design'],
            'Version Control' => ['Version Control'],
            'Continuous Delivery' => ['Continuous Delivery'],
            'Infrastructure as Code' => ['Infrastructure as Code', 'IaC'],
            'Observability' => ['Observability'],
            'Incident Management' => ['Incident Management'],
            'Root Cause Analysis' => ['Root Cause Analysis'],
            'Release Management' => ['Release Management'],
            'Requirements Gathering' => ['Requirements Gathering'],
            'User Story Mapping' => ['User Story Mapping'],
            'Wireframing' => ['Wireframing'],
            'Prototyping' => ['Prototyping'],
            'SEO' => ['SEO', 'Search Engine Optimization'],
            'Email Marketing' => ['Email Marketing'],
            'Content Marketing' => ['Content Marketing'],
            'Social Media Marketing' => ['Social Media Marketing'],
        ],
        'soft_skills' => [
            'Leadership' => ['Leadership'],
            'Teamwork' => ['Teamwork'],
            'Communication' => ['Communication'],
            'Problem Solving' => ['Problem Solving'],
            'Critical Thinking' => ['Critical Thinking'],
            'Time Management' => ['Time Management'],
            'Adaptability' => ['Adaptability', 'Adaptable'],
            'Collaboration' => ['Collaboration'],
            'Project Management' => ['Project Management'],
            'Stakeholder Management' => ['Stakeholder Management'],
            'Conflict Resolution' => ['Conflict Resolution'],
            'Presentation Skills' => ['Presentation Skills', 'Presentations'],
            'Negotiation' => ['Negotiation'],
            'Customer Service' => ['Customer Service'],
            'Attention to Detail' => ['Attention to Detail'],
            'Creativity' => ['Creativity'],
            'Analytical Thinking' => ['Analytical Thinking', 'Analytical Skills'],
            'Cross-functional Collaboration' => ['Cross-functional Collaboration', 'Cross-functional'],
            'Mentoring' => ['Mentoring', 'Mentorship'],
            'Coaching' => ['Coaching'],
            'Public Speaking' => ['Public Speaking'],
            'Written Communication' => ['Written Communication'],
            'Active Listening' => ['Active Listening'],
            'Decision Making' => ['Decision Making'],
            'Strategic Planning' => ['Strategic Planning'],
            'Emotional Intelligence' => ['Emotional Intelligence'],
            'Organizational Skills' => ['Organizational Skills'],
            'Multitasking' => ['Multitasking'],
            'Work Ethic' => ['Work Ethic'],
            'Self-Motivated' => ['Self-Motivated', 'Self-motivated'],
            'Fast Learner' => ['Fast Learner'],
            'Ownership' => ['Ownership'],
            'Empathy' => ['Empathy'],
            'Patience' => ['Patience'],
            'Resilience' => ['Resilience'],
            'Flexibility' => ['Flexibility'],
            'Interpersonal Skills' => ['Interpersonal Skills'],
            'Relationship Building' => ['Relationship Building'],
            'Influencing' => ['Influencing'],
            'Delegation' => ['Delegation'],
            'Facilitation' => ['Facilitation'],
            'Storytelling' => ['Storytelling'],
            'Business Acumen' => ['Business Acumen'],
            'Customer Focus' => ['Customer Focus'],
            'Quality Assurance' => ['Quality Assurance'],
            'Training' => ['Training'],
            'Onboarding' => ['Onboarding'],
            'Team Building' => ['Team Building'],
            'Prioritization' => ['Prioritization'],
            'Resource Planning' => ['Resource Planning'],
        ],
    ];

    /**
     * All skills with their aliases.
     *
     * @return array<string, array<string, list<string>>>
     */
    public function all(): array
    {
        return self::SKILLS;
    }

    /**
     * @return list<string>
     */
    public function categories(): array
    {
        return array_keys(self::SKILLS);
    }

    /**
     * Detect skills mentioned in a block of text.
     *
     * @return list<array{name: string, category: string, count: int, position: int}>
     */
    public function detect(string $text): array
    {
        if (trim($text) === '') {
            return [];
        }

        $found = [];

        foreach (self::SKILLS as $category => $skills) {
            foreach ($skills as $canonical => $aliases) {
                $hits = 0;
                $firstPosition = null;

                foreach ($aliases as $alias) {
                    $alias = trim($alias);
                    if ($alias === '') {
                        continue;
                    }

                    $pattern = '/(?<![A-Za-z0-9+#])' . preg_quote($alias, '/') . '(?![A-Za-z0-9+#])/';
                    $pattern .= $this->isCaseSensitive($alias) ? 'u' : 'iu';

                    $count = preg_match_all($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
                    if ($count === false || $count === 0) {
                        continue;
                    }

                    $hits += $count;
                    $position = $matches[0][0][1];
                    $firstPosition = $firstPosition === null ? $position : min($firstPosition, $position);
                }

                if ($hits > 0 && $firstPosition !== null) {
                    $found[] = [
                        'name' => $canonical,
                        'category' => $category,
                        'count' => $hits,
                        'position' => $firstPosition,
                    ];
                }
            }
        }

        usort($found, static fn (array $a, array $b): int => $a['position'] <=> $b['position']);

        return $found;
    }

    /**
     * @return array<string, string>
     */
    public function categoryLabels(): array
    {
        return self::CATEGORY_LABELS;
    }

    private function isCaseSensitive(string $alias): bool
    {
        $length = mb_strlen($alias);

        if ($length <= 2) {
            return true;
        }

        return $length <= 6 && preg_match('/[A-Z]/', $alias) === 1
            && preg_match('/[a-z]/', $alias) === 0;
    }
}
