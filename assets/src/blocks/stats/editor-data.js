/**
 * Editor data: the languages, the site language, and the installed plugins and themes.
 *
 * @since 2.1.1
 *
 * @type {{languages: Array<{value: string, label: string, name: string}>, siteLanguage: string, plugins: Array<{value: string, label: string}>, themes: Array<{value: string, label: string}>}}
 */
const editorData = window.tstatsBlock || {
	languages: [],
	siteLanguage: '',
	plugins: [],
	themes: [],
};

export default editorData;
