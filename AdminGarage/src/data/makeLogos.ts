export const MAKE_LOGOS: Record<string, string> = {
  Honda: '/BrandLogo/honda logo.png',
  Mitsubishi: '/BrandLogo/mitsu logo.png',
  Hyundai: '/BrandLogo/hyundai-logo.png',
  Nissan: '/BrandLogo/nissan-logo.png',
  Suzuki: '/BrandLogo/suzuki-logo.png',
  Toyota: '/BrandLogo/toyota-logo.png',
  Changan: '/BrandLogo/changan-logo.png',
  Chery: '/BrandLogo/chery-logo.png',
  Chevrolet: '/BrandLogo/chevrolet-logo.png',
  Ford: '/BrandLogo/ford-logo.png',
  Foton: '/BrandLogo/foton-logo.png',
  GAC: '/BrandLogo/gac-logo.png',
  Geely: '/BrandLogo/geely-logo.png',
  JAC: '/BrandLogo/jac-logo.png',
  Jetour: '/BrandLogo/jetour-logo.jpg',
  JMC: '/BrandLogo/jmc-logo.jpg',
  Kia: '/BrandLogo/kia-logo.png',
  'Morris Garage': '/BrandLogo/MG-logo.png',
  Peugeot: '/BrandLogo/peugeot-logo.png',
  SsangYong: '/BrandLogo/ssangyong-logo.png',
  Subaru: '/BrandLogo/subaru-logo.png',
}

export function makeLogoSrc(make: string) {
  const path = MAKE_LOGOS[make]
  return path ? encodeURI(path) : ''
}
