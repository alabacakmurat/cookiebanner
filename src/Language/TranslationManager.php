<?php

declare(strict_types=1);

namespace Chronex\CookieBanner\Language;

class TranslationManager
{
	private string $defaultLanguage = 'en';
	private string $currentLanguage;
	private array $translations = [];
	private array $languageFiles = [];
	private string $defaultLanguagesPath;
	private ?string $customLanguagesPath = null;

	public function __construct(?string $customLanguagesPath = null, string $language = 'en')
	{
		$this->defaultLanguagesPath = dirname(__DIR__, 2) . '/languages';
		$this->customLanguagesPath = $customLanguagesPath;
		$this->currentLanguage = $language;
		$this->loadDefaultLanguages();
	}

	private function loadDefaultLanguages(): void
	{
		// Load built-in translations first
		$this->registerLanguage('en', $this->getEnglishTranslations());
		$this->registerLanguage('tr', $this->getTurkishTranslations());
		$this->registerLanguage('de', $this->getGermanTranslations());
		$this->registerLanguage('fr', $this->getFrenchTranslations());
		$this->registerLanguage('es', $this->getSpanishTranslations());
		$this->registerLanguage('nl', $this->getDutchTranslations());
		$this->registerLanguage('it', $this->getItalianTranslations());
		$this->registerLanguage('pt', $this->getPortugueseTranslations());
		$this->registerLanguage('pl', $this->getPolishTranslations());
		$this->registerLanguage('ru', $this->getRussianTranslations());

		// Load from default library language files
		$this->loadLanguageFilesFromPath($this->defaultLanguagesPath);

		// Load from custom language files (merge/override)
		if ($this->customLanguagesPath) {
			$this->loadLanguageFilesFromPath($this->customLanguagesPath);
		}
	}

	private function loadLanguageFilesFromPath(string $path): void
	{
		if (!is_dir($path)) {
			return;
		}

		// Load PHP files
		$files = glob($path . '/*.php');
		foreach ($files as $file) {
			$lang = basename($file, '.php');
			$translations = include $file;
			if (is_array($translations)) {
				$this->registerLanguage($lang, $translations, true); // Always merge
			}
		}

		// Load JSON files
		$jsonFiles = glob($path . '/*.json');
		foreach ($jsonFiles as $file) {
			$lang = basename($file, '.json');
			$translations = json_decode(file_get_contents($file), true);
			if (is_array($translations)) {
				$this->registerLanguage($lang, $translations, true); // Always merge
			}
		}
	}

	public function registerLanguage(string $code, array $translations, bool $merge = false): self
	{
		if ($merge && isset($this->translations[$code])) {
			$this->translations[$code] = array_merge($this->translations[$code], $translations);
		} else {
			$this->translations[$code] = $translations;
		}
		return $this;
	}

	public function setLanguage(string $code): self
	{
		$this->currentLanguage = $code;
		return $this;
	}

	public function getLanguage(): string
	{
		return $this->currentLanguage;
	}

	public function getAvailableLanguages(): array
	{
		return array_keys($this->translations);
	}

	public function hasLanguage(string $code): bool
	{
		return isset($this->translations[$code]);
	}

	public function get(string $key, ?string $default = null, ?string $language = null): string
	{
		$lang = $language ?? $this->currentLanguage;

		// Try current language
		if (isset($this->translations[$lang][$key])) {
			return $this->translations[$lang][$key];
		}

		// Try language without region (e.g., en-US -> en)
		$baseLang = explode('-', $lang)[0];
		if ($baseLang !== $lang && isset($this->translations[$baseLang][$key])) {
			return $this->translations[$baseLang][$key];
		}

		// Try default language
		if (isset($this->translations[$this->defaultLanguage][$key])) {
			return $this->translations[$this->defaultLanguage][$key];
		}

		// Return default or key
		return $default ?? $key;
	}

	public function getAll(?string $language = null): array
	{
		$lang = $language ?? $this->currentLanguage;
		return $this->translations[$lang] ?? $this->translations[$this->defaultLanguage] ?? [];
	}

	public function extend(string $language, array $translations): self
	{
		if (!isset($this->translations[$language])) {
			$this->translations[$language] = [];
		}
		$this->translations[$language] = array_merge($this->translations[$language], $translations);
		return $this;
	}

	public function setDefaultLanguage(string $code): self
	{
		$this->defaultLanguage = $code;
		return $this;
	}

	private function getEnglishTranslations(): array
	{
		return [
			'title' => 'Cookie Settings',
			'description' => 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. By clicking "Accept All", you consent to our use of cookies.',
			'short_description' => 'This site uses cookies to improve your experience.',
			'accept_all' => 'Accept All',
			'reject_all' => 'Reject All',
			'accept' => 'Accept',
			'decline' => 'Decline',
			'reject' => 'Reject',
			'save' => 'Save',
			'save_preferences' => 'Save Preferences',
			'preferences' => 'Preferences',
			'customize' => 'Customize',
			'settings' => 'Settings',
			'cookie_settings' => 'Cookie Settings',
			'close' => 'Close',
			'learn_more' => 'Learn more',
			'privacy_policy' => 'Privacy Policy',
			'cookie_policy' => 'Cookie Policy',
			'preferences_title' => 'Cookie Preferences',
			'preferences_description' => 'Manage your cookie preferences below. You can enable or disable different types of cookies.',
			'always_active' => 'Always Active',
			'required' => 'Required',
			'category_necessary_title' => 'Necessary',
			'category_necessary_description' => 'Essential cookies required for the website to function properly. These cannot be disabled.',
			'category_functional_title' => 'Functional',
			'category_functional_description' => 'Cookies that enable enhanced functionality and personalization.',
			'category_analytics_title' => 'Analytics',
			'category_analytics_description' => 'Cookies that help us understand how visitors interact with our website.',
			'category_marketing_title' => 'Marketing',
			'category_marketing_description' => 'Cookies used to track visitors across websites for marketing purposes.',
			'category_advertising_title' => 'Advertising',
			'category_advertising_description' => 'Cookies used to display personalized advertisements.',
			// Blocking mode translations
			'blocking_title' => 'Cookie Consent Required',
			'blocking_message' => 'To access this website, you must accept our cookie policy. We use cookies to ensure the basic functionality of the site and to enhance your experience.',
			'blocking_categories_title' => 'We use the following types of cookies:',
			'blocking_warning' => 'You cannot use this website without accepting at least the required cookies.',
		];
	}

	private function getTurkishTranslations(): array
	{
		return [
			'title' => 'Çerez Ayarları',
			'description' => 'Tarama deneyiminizi geliştirmek, kişiselleştirilmiş içerik sunmak ve trafiğimizi analiz etmek için çerezler kullanıyoruz. "Tümünü Kabul Et" seçeneğine tıklayarak çerez kullanımımızı onaylıyorsunuz.',
			'short_description' => 'Bu site deneyiminizi iyileştirmek için çerezler kullanır.',
			'accept_all' => 'Tümünü Kabul Et',
			'reject_all' => 'Tümünü Reddet',
			'accept' => 'Kabul Et',
			'decline' => 'Reddet',
			'reject' => 'Reddet',
			'save' => 'Kaydet',
			'save_preferences' => 'Tercihleri Kaydet',
			'preferences' => 'Tercihler',
			'customize' => 'Özelleştir',
			'settings' => 'Ayarlar',
			'cookie_settings' => 'Çerez Ayarları',
			'close' => 'Kapat',
			'learn_more' => 'Daha fazla bilgi',
			'privacy_policy' => 'Gizlilik Politikası',
			'cookie_policy' => 'Çerez Politikası',
			'preferences_title' => 'Çerez Tercihleri',
			'preferences_description' => 'Çerez tercihlerinizi aşağıdan yönetin. Farklı çerez türlerini etkinleştirebilir veya devre dışı bırakabilirsiniz.',
			'always_active' => 'Her Zaman Aktif',
			'required' => 'Zorunlu',
			'category_necessary_title' => 'Zorunlu',
			'category_necessary_description' => 'Web sitesinin düzgün çalışması için gerekli olan temel çerezler. Bunlar devre dışı bırakılamaz.',
			'category_functional_title' => 'İşlevsel',
			'category_functional_description' => 'Gelişmiş işlevsellik ve kişiselleştirme sağlayan çerezler.',
			'category_analytics_title' => 'Analitik',
			'category_analytics_description' => 'Ziyaretçilerin web sitemizle nasıl etkileşime girdiğini anlamamıza yardımcı olan çerezler.',
			'category_marketing_title' => 'Pazarlama',
			'category_marketing_description' => 'Pazarlama amaçları için ziyaretçileri web siteleri arasında izlemek için kullanılan çerezler.',
			'category_advertising_title' => 'Reklam',
			'category_advertising_description' => 'Kişiselleştirilmiş reklamlar görüntülemek için kullanılan çerezler.',
			// Blocking mode translations
			'blocking_title' => 'Çerez Onayı Gerekli',
			'blocking_message' => 'Bu web sitesine erişmek için çerez politikamızı kabul etmeniz gerekmektedir. Sitenin temel işlevselliğini sağlamak ve deneyiminizi iyileştirmek için çerezler kullanıyoruz.',
			'blocking_categories_title' => 'Aşağıdaki türde çerezler kullanıyoruz:',
			'blocking_warning' => 'En azından zorunlu çerezleri kabul etmeden bu web sitesini kullanamazsınız.',
		];
	}

	private function getGermanTranslations(): array
	{
		return [
			'title' => 'Cookie-Einstellungen',
			'description' => 'Wir verwenden Cookies, um Ihr Browsererlebnis zu verbessern, personalisierte Inhalte bereitzustellen und unseren Datenverkehr zu analysieren. Durch Klicken auf "Alle akzeptieren" stimmen Sie unserer Verwendung von Cookies zu.',
			'short_description' => 'Diese Seite verwendet Cookies, um Ihr Erlebnis zu verbessern.',
			'accept_all' => 'Alle akzeptieren',
			'reject_all' => 'Alle ablehnen',
			'accept' => 'Akzeptieren',
			'decline' => 'Ablehnen',
			'reject' => 'Ablehnen',
			'save' => 'Speichern',
			'save_preferences' => 'Einstellungen speichern',
			'preferences' => 'Einstellungen',
			'customize' => 'Anpassen',
			'settings' => 'Einstellungen',
			'cookie_settings' => 'Cookie-Einstellungen',
			'close' => 'Schließen',
			'learn_more' => 'Mehr erfahren',
			'privacy_policy' => 'Datenschutz',
			'cookie_policy' => 'Cookie-Richtlinie',
			'preferences_title' => 'Cookie-Präferenzen',
			'preferences_description' => 'Verwalten Sie unten Ihre Cookie-Einstellungen. Sie können verschiedene Cookie-Typen aktivieren oder deaktivieren.',
			'always_active' => 'Immer aktiv',
			'required' => 'Erforderlich',
			'category_necessary_title' => 'Notwendig',
			'category_necessary_description' => 'Wesentliche Cookies, die für das ordnungsgemäße Funktionieren der Website erforderlich sind.',
			'category_functional_title' => 'Funktional',
			'category_functional_description' => 'Cookies, die erweiterte Funktionalität und Personalisierung ermöglichen.',
			'category_analytics_title' => 'Analytik',
			'category_analytics_description' => 'Cookies, die uns helfen zu verstehen, wie Besucher mit unserer Website interagieren.',
			'category_marketing_title' => 'Marketing',
			'category_marketing_description' => 'Cookies, die verwendet werden, um Besucher über Websites hinweg für Marketingzwecke zu verfolgen.',
			'category_advertising_title' => 'Werbung',
			'category_advertising_description' => 'Cookies, die zur Anzeige personalisierter Werbung verwendet werden.',
			// Blocking mode translations
			'blocking_title' => 'Cookie-Zustimmung erforderlich',
			'blocking_message' => 'Um auf diese Website zugreifen zu können, müssen Sie unsere Cookie-Richtlinie akzeptieren. Wir verwenden Cookies, um die grundlegende Funktionalität der Website zu gewährleisten und Ihre Erfahrung zu verbessern.',
			'blocking_categories_title' => 'Wir verwenden die folgenden Arten von Cookies:',
			'blocking_warning' => 'Sie können diese Website nicht nutzen, ohne mindestens die erforderlichen Cookies zu akzeptieren.',
		];
	}

	private function getFrenchTranslations(): array
	{
		return [
			'title' => 'Paramètres des cookies',
			'description' => 'Nous utilisons des cookies pour améliorer votre expérience de navigation, proposer du contenu personnalisé et analyser notre trafic. En cliquant sur "Tout accepter", vous consentez à notre utilisation des cookies.',
			'short_description' => 'Ce site utilise des cookies pour améliorer votre expérience.',
			'accept_all' => 'Tout accepter',
			'reject_all' => 'Tout refuser',
			'accept' => 'Accepter',
			'decline' => 'Refuser',
			'reject' => 'Refuser',
			'save' => 'Enregistrer',
			'save_preferences' => 'Enregistrer les préférences',
			'preferences' => 'Préférences',
			'customize' => 'Personnaliser',
			'settings' => 'Paramètres',
			'cookie_settings' => 'Paramètres des cookies',
			'close' => 'Fermer',
			'learn_more' => 'En savoir plus',
			'privacy_policy' => 'Politique de confidentialité',
			'cookie_policy' => 'Politique des cookies',
			'preferences_title' => 'Préférences de cookies',
			'preferences_description' => 'Gérez vos préférences de cookies ci-dessous. Vous pouvez activer ou désactiver différents types de cookies.',
			'always_active' => 'Toujours actif',
			'required' => 'Requis',
			'category_necessary_title' => 'Nécessaires',
			'category_necessary_description' => 'Cookies essentiels au bon fonctionnement du site web.',
			'category_functional_title' => 'Fonctionnels',
			'category_functional_description' => 'Cookies permettant des fonctionnalités améliorées et la personnalisation.',
			'category_analytics_title' => 'Analytiques',
			'category_analytics_description' => 'Cookies nous aidant à comprendre comment les visiteurs interagissent avec notre site.',
			'category_marketing_title' => 'Marketing',
			'category_marketing_description' => 'Cookies utilisés pour suivre les visiteurs sur les sites web à des fins marketing.',
			'category_advertising_title' => 'Publicitaires',
			'category_advertising_description' => 'Cookies utilisés pour afficher des publicités personnalisées.',
			// Blocking mode translations
			'blocking_title' => 'Consentement aux cookies requis',
			'blocking_message' => 'Pour accéder à ce site web, vous devez accepter notre politique de cookies. Nous utilisons des cookies pour assurer le bon fonctionnement du site et améliorer votre expérience.',
			'blocking_categories_title' => 'Nous utilisons les types de cookies suivants :',
			'blocking_warning' => 'Vous ne pouvez pas utiliser ce site web sans accepter au moins les cookies requis.',
		];
	}

	private function getSpanishTranslations(): array
	{
		return [
			'title' => 'Configuración de cookies',
			'description' => 'Utilizamos cookies para mejorar su experiencia de navegación, ofrecer contenido personalizado y analizar nuestro tráfico. Al hacer clic en "Aceptar todo", acepta nuestro uso de cookies.',
			'short_description' => 'Este sitio utiliza cookies para mejorar su experiencia.',
			'accept_all' => 'Aceptar todo',
			'reject_all' => 'Rechazar todo',
			'accept' => 'Aceptar',
			'decline' => 'Rechazar',
			'reject' => 'Rechazar',
			'save' => 'Guardar',
			'save_preferences' => 'Guardar preferencias',
			'preferences' => 'Preferencias',
			'customize' => 'Personalizar',
			'settings' => 'Configuración',
			'cookie_settings' => 'Configuración de cookies',
			'close' => 'Cerrar',
			'learn_more' => 'Más información',
			'privacy_policy' => 'Política de privacidad',
			'cookie_policy' => 'Política de cookies',
			'preferences_title' => 'Preferencias de cookies',
			'preferences_description' => 'Gestione sus preferencias de cookies a continuación. Puede activar o desactivar diferentes tipos de cookies.',
			'always_active' => 'Siempre activo',
			'required' => 'Requerido',
			'category_necessary_title' => 'Necesarias',
			'category_necessary_description' => 'Cookies esenciales para el correcto funcionamiento del sitio web.',
			'category_functional_title' => 'Funcionales',
			'category_functional_description' => 'Cookies que permiten funcionalidades mejoradas y personalización.',
			'category_analytics_title' => 'Analíticas',
			'category_analytics_description' => 'Cookies que nos ayudan a entender cómo los visitantes interactúan con nuestro sitio.',
			'category_marketing_title' => 'Marketing',
			'category_marketing_description' => 'Cookies utilizadas para rastrear visitantes en sitios web con fines de marketing.',
			'category_advertising_title' => 'Publicidad',
			'category_advertising_description' => 'Cookies utilizadas para mostrar anuncios personalizados.',
			// Blocking mode translations
			'blocking_title' => 'Se requiere consentimiento de cookies',
			'blocking_message' => 'Para acceder a este sitio web, debe aceptar nuestra política de cookies. Utilizamos cookies para garantizar la funcionalidad básica del sitio y mejorar su experiencia.',
			'blocking_categories_title' => 'Utilizamos los siguientes tipos de cookies:',
			'blocking_warning' => 'No puede utilizar este sitio web sin aceptar al menos las cookies requeridas.',
		];
	}

	private function getDutchTranslations(): array
	{
		return [
			'title' => 'Cookie-instellingen',
			'description' => 'We gebruiken cookies om uw browse-ervaring te verbeteren, gepersonaliseerde inhoud aan te bieden en ons verkeer te analyseren. Door op "Alles accepteren" te klikken, stemt u in met ons gebruik van cookies.',
			'short_description' => 'Deze site gebruikt cookies om uw ervaring te verbeteren.',
			'accept_all' => 'Alles accepteren',
			'reject_all' => 'Alles weigeren',
			'accept' => 'Accepteren',
			'decline' => 'Weigeren',
			'reject' => 'Weigeren',
			'save' => 'Opslaan',
			'save_preferences' => 'Voorkeuren opslaan',
			'preferences' => 'Voorkeuren',
			'customize' => 'Aanpassen',
			'settings' => 'Instellingen',
			'cookie_settings' => 'Cookie-instellingen',
			'close' => 'Sluiten',
			'learn_more' => 'Meer informatie',
			'privacy_policy' => 'Privacybeleid',
			'cookie_policy' => 'Cookiebeleid',
			'preferences_title' => 'Cookievoorkeuren',
			'preferences_description' => 'Beheer hieronder uw cookievoorkeuren. U kunt verschillende soorten cookies in- of uitschakelen.',
			'always_active' => 'Altijd actief',
			'required' => 'Vereist',
			'category_necessary_title' => 'Noodzakelijk',
			'category_necessary_description' => 'Essentiële cookies die nodig zijn voor de goede werking van de website.',
			'category_functional_title' => 'Functioneel',
			'category_functional_description' => 'Cookies die verbeterde functionaliteit en personalisatie mogelijk maken.',
			'category_analytics_title' => 'Analytisch',
			'category_analytics_description' => 'Cookies die ons helpen begrijpen hoe bezoekers omgaan met onze website.',
			'category_marketing_title' => 'Marketing',
			'category_marketing_description' => 'Cookies die worden gebruikt om bezoekers op websites te volgen voor marketingdoeleinden.',
			'category_advertising_title' => 'Adverteren',
			'category_advertising_description' => 'Cookies die worden gebruikt om gepersonaliseerde advertenties weer te geven.',
			// Blocking mode translations
			'blocking_title' => 'Cookie-toestemming vereist',
			'blocking_message' => 'Om toegang te krijgen tot deze website, moet u ons cookiebeleid accepteren. We gebruiken cookies om de basisfunctionaliteit van de site te garanderen en uw ervaring te verbeteren.',
			'blocking_categories_title' => 'We gebruiken de volgende soorten cookies:',
			'blocking_warning' => 'U kunt deze website niet gebruiken zonder ten minste de vereiste cookies te accepteren.',
		];
	}

	private function getItalianTranslations(): array
	{
		return [
			'title' => 'Impostazioni cookie',
			'description' => 'Utilizziamo i cookie per migliorare la tua esperienza di navigazione, offrire contenuti personalizzati e analizzare il nostro traffico. Cliccando su "Accetta tutto", acconsenti al nostro utilizzo dei cookie.',
			'short_description' => 'Questo sito utilizza i cookie per migliorare la tua esperienza.',
			'accept_all' => 'Accetta tutto',
			'reject_all' => 'Rifiuta tutto',
			'accept' => 'Accetta',
			'decline' => 'Rifiuta',
			'reject' => 'Rifiuta',
			'save' => 'Salva',
			'save_preferences' => 'Salva preferenze',
			'preferences' => 'Preferenze',
			'customize' => 'Personalizza',
			'settings' => 'Impostazioni',
			'cookie_settings' => 'Impostazioni cookie',
			'close' => 'Chiudi',
			'learn_more' => 'Scopri di più',
			'privacy_policy' => 'Informativa sulla privacy',
			'cookie_policy' => 'Politica sui cookie',
			'preferences_title' => 'Preferenze cookie',
			'preferences_description' => 'Gestisci le tue preferenze sui cookie qui sotto. Puoi abilitare o disabilitare diversi tipi di cookie.',
			'always_active' => 'Sempre attivo',
			'required' => 'Richiesto',
			'category_necessary_title' => 'Necessari',
			'category_necessary_description' => 'Cookie essenziali per il corretto funzionamento del sito web.',
			'category_functional_title' => 'Funzionali',
			'category_functional_description' => 'Cookie che abilitano funzionalità avanzate e personalizzazione.',
			'category_analytics_title' => 'Analitici',
			'category_analytics_description' => 'Cookie che ci aiutano a capire come i visitatori interagiscono con il nostro sito.',
			'category_marketing_title' => 'Marketing',
			'category_marketing_description' => 'Cookie utilizzati per tracciare i visitatori sui siti web per scopi di marketing.',
			'category_advertising_title' => 'Pubblicità',
			'category_advertising_description' => 'Cookie utilizzati per visualizzare annunci personalizzati.',
			// Blocking mode translations
			'blocking_title' => 'Consenso ai cookie richiesto',
			'blocking_message' => 'Per accedere a questo sito web, devi accettare la nostra politica sui cookie. Utilizziamo i cookie per garantire la funzionalità di base del sito e migliorare la tua esperienza.',
			'blocking_categories_title' => 'Utilizziamo i seguenti tipi di cookie:',
			'blocking_warning' => 'Non puoi utilizzare questo sito web senza accettare almeno i cookie richiesti.',
		];
	}

	private function getPortugueseTranslations(): array
	{
		return [
			'title' => 'Configurações de cookies',
			'description' => 'Usamos cookies para melhorar sua experiência de navegação, fornecer conteúdo personalizado e analisar nosso tráfego. Ao clicar em "Aceitar tudo", você consente com nosso uso de cookies.',
			'short_description' => 'Este site usa cookies para melhorar sua experiência.',
			'accept_all' => 'Aceitar tudo',
			'reject_all' => 'Rejeitar tudo',
			'accept' => 'Aceitar',
			'decline' => 'Recusar',
			'reject' => 'Rejeitar',
			'save' => 'Salvar',
			'save_preferences' => 'Salvar preferências',
			'preferences' => 'Preferências',
			'customize' => 'Personalizar',
			'settings' => 'Configurações',
			'cookie_settings' => 'Configurações de cookies',
			'close' => 'Fechar',
			'learn_more' => 'Saiba mais',
			'privacy_policy' => 'Política de privacidade',
			'cookie_policy' => 'Política de cookies',
			'preferences_title' => 'Preferências de cookies',
			'preferences_description' => 'Gerencie suas preferências de cookies abaixo. Você pode ativar ou desativar diferentes tipos de cookies.',
			'always_active' => 'Sempre ativo',
			'required' => 'Obrigatório',
			'category_necessary_title' => 'Necessários',
			'category_necessary_description' => 'Cookies essenciais para o funcionamento adequado do site.',
			'category_functional_title' => 'Funcionais',
			'category_functional_description' => 'Cookies que permitem funcionalidades aprimoradas e personalização.',
			'category_analytics_title' => 'Analíticos',
			'category_analytics_description' => 'Cookies que nos ajudam a entender como os visitantes interagem com nosso site.',
			'category_marketing_title' => 'Marketing',
			'category_marketing_description' => 'Cookies usados para rastrear visitantes em sites para fins de marketing.',
			'category_advertising_title' => 'Publicidade',
			'category_advertising_description' => 'Cookies usados para exibir anúncios personalizados.',
			// Blocking mode translations
			'blocking_title' => 'Consentimento de cookies necessário',
			'blocking_message' => 'Para acessar este site, você deve aceitar nossa política de cookies. Usamos cookies para garantir a funcionalidade básica do site e melhorar sua experiência.',
			'blocking_categories_title' => 'Usamos os seguintes tipos de cookies:',
			'blocking_warning' => 'Você não pode usar este site sem aceitar pelo menos os cookies necessários.',
		];
	}

	private function getPolishTranslations(): array
	{
		return [
			'title' => 'Ustawienia plików cookie',
			'description' => 'Używamy plików cookie, aby ulepszyć przeglądanie, dostarczać spersonalizowane treści i analizować nasz ruch. Klikając "Zaakceptuj wszystko", wyrażasz zgodę na używanie przez nas plików cookie.',
			'short_description' => 'Ta strona używa plików cookie, aby poprawić Twoje doświadczenia.',
			'accept_all' => 'Zaakceptuj wszystko',
			'reject_all' => 'Odrzuć wszystko',
			'accept' => 'Akceptuj',
			'decline' => 'Odmów',
			'reject' => 'Odrzuć',
			'save' => 'Zapisz',
			'save_preferences' => 'Zapisz preferencje',
			'preferences' => 'Preferencje',
			'customize' => 'Dostosuj',
			'settings' => 'Ustawienia',
			'cookie_settings' => 'Ustawienia plików cookie',
			'close' => 'Zamknij',
			'learn_more' => 'Dowiedz się więcej',
			'privacy_policy' => 'Polityka prywatności',
			'cookie_policy' => 'Polityka plików cookie',
			'preferences_title' => 'Preferencje plików cookie',
			'preferences_description' => 'Zarządzaj swoimi preferencjami plików cookie poniżej. Możesz włączyć lub wyłączyć różne rodzaje plików cookie.',
			'always_active' => 'Zawsze aktywne',
			'required' => 'Wymagane',
			'category_necessary_title' => 'Niezbędne',
			'category_necessary_description' => 'Niezbędne pliki cookie wymagane do prawidłowego działania strony.',
			'category_functional_title' => 'Funkcjonalne',
			'category_functional_description' => 'Pliki cookie umożliwiające rozszerzoną funkcjonalność i personalizację.',
			'category_analytics_title' => 'Analityczne',
			'category_analytics_description' => 'Pliki cookie pomagające nam zrozumieć, jak odwiedzający korzystają z naszej strony.',
			'category_marketing_title' => 'Marketingowe',
			'category_marketing_description' => 'Pliki cookie używane do śledzenia odwiedzających w celach marketingowych.',
			'category_advertising_title' => 'Reklamowe',
			'category_advertising_description' => 'Pliki cookie używane do wyświetlania spersonalizowanych reklam.',
			// Blocking mode translations
			'blocking_title' => 'Wymagana zgoda na pliki cookie',
			'blocking_message' => 'Aby uzyskać dostęp do tej strony, musisz zaakceptować naszą politykę plików cookie. Używamy plików cookie, aby zapewnić podstawową funkcjonalność strony i poprawić Twoje doświadczenia.',
			'blocking_categories_title' => 'Używamy następujących rodzajów plików cookie:',
			'blocking_warning' => 'Nie możesz korzystać z tej strony bez zaakceptowania przynajmniej wymaganych plików cookie.',
		];
	}

	private function getRussianTranslations(): array
	{
		return [
			'title' => 'Настройки cookie',
			'description' => 'Мы используем файлы cookie для улучшения работы сайта, предоставления персонализированного контента и анализа трафика. Нажимая "Принять все", вы соглашаетесь на использование файлов cookie.',
			'short_description' => 'Этот сайт использует файлы cookie для улучшения вашего опыта.',
			'accept_all' => 'Принять все',
			'reject_all' => 'Отклонить все',
			'accept' => 'Принять',
			'decline' => 'Отклонить',
			'reject' => 'Отклонить',
			'save' => 'Сохранить',
			'save_preferences' => 'Сохранить настройки',
			'preferences' => 'Настройки',
			'customize' => 'Настроить',
			'settings' => 'Настройки',
			'cookie_settings' => 'Настройки cookie',
			'close' => 'Закрыть',
			'learn_more' => 'Узнать больше',
			'privacy_policy' => 'Политика конфиденциальности',
			'cookie_policy' => 'Политика cookie',
			'preferences_title' => 'Настройки cookie',
			'preferences_description' => 'Управляйте настройками cookie ниже. Вы можете включить или отключить различные типы файлов cookie.',
			'always_active' => 'Всегда активны',
			'required' => 'Обязательно',
			'category_necessary_title' => 'Необходимые',
			'category_necessary_description' => 'Основные файлы cookie, необходимые для правильной работы сайта.',
			'category_functional_title' => 'Функциональные',
			'category_functional_description' => 'Файлы cookie для расширенной функциональности и персонализации.',
			'category_analytics_title' => 'Аналитические',
			'category_analytics_description' => 'Файлы cookie, помогающие понять, как посетители взаимодействуют с сайтом.',
			'category_marketing_title' => 'Маркетинговые',
			'category_marketing_description' => 'Файлы cookie для отслеживания посетителей в маркетинговых целях.',
			'category_advertising_title' => 'Рекламные',
			'category_advertising_description' => 'Файлы cookie для показа персонализированной рекламы.',
			// Blocking mode translations
			'blocking_title' => 'Требуется согласие на использование cookie',
			'blocking_message' => 'Для доступа к этому сайту вы должны принять нашу политику использования файлов cookie. Мы используем файлы cookie для обеспечения базовой функциональности сайта и улучшения вашего опыта.',
			'blocking_categories_title' => 'Мы используем следующие типы файлов cookie:',
			'blocking_warning' => 'Вы не можете использовать этот сайт, не приняв хотя бы обязательные файлы cookie.',
		];
	}
}
