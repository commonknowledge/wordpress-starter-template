const pad = (value: number) => String(value).padStart(2, "0");

/**
 * Format a date as HH:MM:SS, matching the PHP `H:i:s` format used by
 * render.php so the server-rendered time and the ticking time look identical.
 */
export function formatTime(date: Date): string {
  return [date.getHours(), date.getMinutes(), date.getSeconds()]
    .map(pad)
    .join(":");
}
