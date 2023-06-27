import { test, expect } from '@playwright/test';

test('Rellena Formulario', async ({ page }) => {
	await page.goto('http://wilapp.local/');
	await page.getByRole('link', { name: 'Sample Page' }).click();
	await page.getByText('Peluquería').click();
	await page.getByText('Corte femenino').click();
	await page.getByText('Viernes 23-06-2023').click();
	await page.getByText('Antonio').click();
	await page.getByText('09:15').click();
	await page.locator('#wilapp-name').fill('nombre pruebas');
	await page.locator('#wilapp-phone').fill('669904426');
	await page.locator('#wilapp-email').fill('pruebas@pruebas.com');
	await page.locator('#wilapp-notes').fill('nota');
	await page.getByText('He leído y acepto los términos y condiciones y la política de privacidad.').click();
	await page.getByRole('link', { name: ' Confirmar' }).click();

  // Expect a title "to contain" a substring.
  await expect(page.getByText('Cita creada correctamente')).toBeVisible();
});
