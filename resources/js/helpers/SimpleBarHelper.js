export default class SimpleBarHelper {
    /**
     * Aplica o SimpleBar a todos os elementos com a classe 'scrollbar'.
     */
    static apply() {
        const elements = document.querySelectorAll('.scrollbar');
        elements.forEach(element => {
            new SimpleBar(element);
        });
    }
}
