export function useConfirm(prompt) {
    return () => window.confirm(prompt);
}
