export interface Product {
  id: string;
  name: string;
  description: string;
  specs: Record<string, string>;
  features: string[];
  price: number;
  brand: string;
  category: string;
  images: string[];
  isHidden: boolean;
  isNew: boolean;
  isFeatured: boolean;
}

export interface Category {
  id: string;
  name: string;
  nameAr: string;
  nameKu: string;
  image: string;
  icon: string;
}

export interface Brand {
  id: string;
  name: string;
}

export const brands: Brand[] = [
  { id: 'b1', name: 'Chrani Pro' },
  { id: 'b2', name: 'Chrani Elite' },
  { id: 'b3', name: 'Chrani Home' },
  { id: 'b4', name: 'Chrani Studio' },
];

export const categories: Category[] = [
  { id: 'c1', name: 'Refrigerators', nameAr: 'ثلاجات', nameKu: 'فریزەر', image: 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=600&q=80', icon: 'Refrigerator' },
  { id: 'c2', name: 'Washing Machines', nameAr: 'غسالات', nameKu: 'جلشۆر', image: 'https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=600&q=80', icon: 'WashingMachine' },
  { id: 'c3', name: 'Air Conditioners', nameAr: 'مكيفات', nameKu: 'سارکەر', image: 'https://images.unsplash.com/photo-1631567091196-4f7ff8055aa7?w=600&q=80', icon: 'AirVent' },
  { id: 'c4', name: 'Ovens & Cookers', nameAr: 'أفران و طباخات', nameKu: 'فڕن و تەنوور', image: 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=600&q=80', icon: 'Flame' },
  { id: 'c5', name: 'Dishwashers', nameAr: 'غسالات صحون', nameKu: 'قاپشۆر', image: 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=600&q=80', icon: 'Droplets' },
  { id: 'c6', name: 'Small Appliances', nameAr: 'أجهزة صغيرة', nameKu: 'ئامێرە بچووکەکان', image: 'https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=600&q=80', icon: 'Zap' },
];

export const products: Product[] = [
  {
    id: 'p1', name: 'Frost-Free French Door Refrigerator', description: 'Experience premium cooling with our flagship French door refrigerator. Featuring a spacious 650L capacity, multi-airflow system, and an elegant stainless-steel finish that complements any modern kitchen.', specs: { Capacity: '650 Liters', Dimensions: '91 × 72 × 178 cm', 'Energy Rating': 'A++', 'Cooling Type': 'Frost-Free', Color: 'Stainless Steel', Warranty: '10 Years Compressor' }, features: ['Multi-airflow cooling system', 'Inverter compressor technology', 'LED interior lighting', 'Convertible freezer zone', 'Humidity-controlled crisper', 'Built-in water dispenser'], price: 1899, brand: 'Chrani Pro', category: 'Refrigerators', images: ['https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=800&q=80', 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=800&q=80', 'https://images.unsplash.com/photo-1536353284924-9220c464e262?w=800&q=80'], isHidden: false, isNew: false, isFeatured: true,
  },
  {
    id: 'p2', name: 'Smart Front-Load Washing Machine', description: 'Advanced front-load washer with AI-powered wash cycles that automatically detect fabric type and soil level. Ultra-quiet operation makes it perfect for open-plan living.', specs: { Capacity: '10 kg', Spin: '1400 RPM', Programs: '16', 'Energy Rating': 'A+++', Display: 'LED Touch', Warranty: '5 Years' }, features: ['AI wash technology', 'Steam hygiene cycle', 'Quick wash 15 min', 'Child lock safety', 'Delay start timer', 'Anti-vibration design'], price: 1299, brand: 'Chrani Elite', category: 'Washing Machines', images: ['https://images.unsplash.com/photo-1626806787461-102c1bfaaea1?w=800&q=80', 'https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?w=800&q=80'], isHidden: false, isNew: true, isFeatured: true,
  },
  {
    id: 'p3', name: 'Inverter Split Air Conditioner', description: 'Powerful yet whisper-quiet split AC with advanced inverter technology. Cools large rooms efficiently while maintaining optimal humidity levels.', specs: { Capacity: '24,000 BTU', 'Energy Rating': 'A+', 'Noise Level': '22 dB', 'Room Size': 'Up to 40 m²', Refrigerant: 'R32 Eco', Warranty: '7 Years Compressor' }, features: ['Turbo cooling mode', 'Self-cleaning function', 'Wi-Fi smart control', 'Sleep mode', 'Anti-bacterial filter', '4-way airflow'], price: 899, brand: 'Chrani Pro', category: 'Air Conditioners', images: ['https://images.unsplash.com/photo-1631567091196-4f7ff8055aa7?w=800&q=80'], isHidden: false, isNew: true, isFeatured: false,
  },
  {
    id: 'p4', name: 'Built-In Electric Oven', description: 'Professional-grade built-in oven with precise temperature control and 12 cooking functions. The pyrolytic self-cleaning feature makes maintenance effortless.', specs: { Capacity: '75 Liters', Functions: '12', 'Max Temp': '300°C', 'Energy Rating': 'A+', Type: 'Convection + Grill', Warranty: '3 Years' }, features: ['Pyrolytic self-cleaning', 'Rapid preheat', 'Telescopic rails', 'Digital timer', 'Triple-glazed door', 'Rotisserie function'], price: 749, brand: 'Chrani Studio', category: 'Ovens & Cookers', images: ['https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=800&q=80', 'https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800&q=80'], isHidden: false, isNew: false, isFeatured: true,
  },
  {
    id: 'p5', name: 'Freestanding Dishwasher', description: 'Efficient 14-place setting dishwasher with multiple wash programs and a half-load option for smaller loads. AquaStop technology prevents water damage.', specs: { Capacity: '14 Place Settings', Programs: '8', 'Energy Rating': 'A++', 'Noise Level': '44 dB', 'Water Usage': '9.5 L/cycle', Warranty: '3 Years' }, features: ['AquaStop leak protection', 'Half-load option', 'Intensive zone', 'Delay start 24h', 'Stainless steel tub', 'Auto door opening dry'], price: 699, brand: 'Chrani Home', category: 'Dishwashers', images: ['https://images.unsplash.com/photo-1585771724684-38269d6639fd?w=800&q=80'], isHidden: false, isNew: false, isFeatured: false,
  },
  {
    id: 'p6', name: 'Premium Stand Mixer', description: 'A powerful 1200W stand mixer with a 6.5L stainless steel bowl. Ideal for baking enthusiasts who demand professional results at home.', specs: { Power: '1200W', 'Bowl Capacity': '6.5 Liters', Speeds: '10 + Pulse', Material: 'Die-cast Metal', Attachments: '3 Included', Warranty: '2 Years' }, features: ['Planetary mixing action', 'Splash guard included', 'Tilt-head design', 'Dishwasher-safe attachments', 'Non-slip suction feet', 'Available in 5 colors'], price: 349, brand: 'Chrani Studio', category: 'Small Appliances', images: ['https://images.unsplash.com/photo-1570222094114-d054a817e56b?w=800&q=80'], isHidden: false, isNew: true, isFeatured: true,
  },
  {
    id: 'p7', name: 'Side-by-Side Refrigerator', description: 'Spacious side-by-side refrigerator with external ice and water dispenser. Perfect for large families who need maximum storage capacity.', specs: { Capacity: '580 Liters', Dimensions: '91 × 72 × 178 cm', 'Energy Rating': 'A+', 'Cooling Type': 'No Frost', Color: 'Black Steel', Warranty: '10 Years Compressor' }, features: ['External ice dispenser', 'Water filter system', 'Door alarm', 'Quick freeze', 'LED display', 'Multi-airflow system'], price: 1599, brand: 'Chrani Pro', category: 'Refrigerators', images: ['https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?w=800&q=80', 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?w=800&q=80'], isHidden: false, isNew: false, isFeatured: false,
  },
  {
    id: 'p8', name: 'Top-Load Washing Machine', description: 'Compact and efficient top-load washer perfect for smaller spaces. Features a durable stainless steel drum and whisper-quiet motor.', specs: { Capacity: '8 kg', Spin: '1100 RPM', Programs: '10', 'Energy Rating': 'A++', Display: 'LED', Warranty: '3 Years' }, features: ['Soft-close lid', 'Soak function', 'Fuzzy logic control', 'Child lock', 'Delay timer', 'Self-clean drum'], price: 549, brand: 'Chrani Home', category: 'Washing Machines', images: ['https://images.unsplash.com/photo-1610557892470-55d9e80c0bce?w=800&q=80'], isHidden: false, isNew: false, isFeatured: false,
  },
  { id: 'p9', name: 'Hidden Test Product', description: 'Should not appear', specs: {}, features: [], price: 0, brand: 'Chrani', category: 'Test', images: [], isHidden: true, isNew: false, isFeatured: false },
  {
    id: 'p10', name: 'Portable Air Conditioner', description: 'Versatile portable AC unit perfect for rooms without external unit installation. Easy to move with built-in casters and a sleek modern design.', specs: { Capacity: '12,000 BTU', 'Energy Rating': 'A', Modes: 'Cool / Fan / Dry', Coverage: 'Up to 25 m²', 'Noise Level': '52 dB', Warranty: '2 Years' }, features: ['3-in-1 functionality', 'Remote control', '24h timer', 'Auto-evaporation', 'Washable filter', 'LED display'], price: 499, brand: 'Chrani Home', category: 'Air Conditioners', images: ['https://images.unsplash.com/photo-1631567091196-4f7ff8055aa7?w=800&q=80'], isHidden: false, isNew: true, isFeatured: false,
  },
];

export const getVisibleProducts = () => products.filter(p => !p.isHidden);
export const getFeaturedProducts = () => getVisibleProducts().filter(p => p.isFeatured);
export const getNewArrivals = () => getVisibleProducts().filter(p => p.isNew);

export const serviceCenters = [
  { id: 's1', name: 'Chrani Service Center - Erbil', address: '100m Street, Near Ankawa Intersection, Erbil, Kurdistan Region', phone: '+964 750 123 4567' },
  { id: 's2', name: 'Chrani Service Center - Baghdad', address: 'Al-Karada District, Street 52, Baghdad, Iraq', phone: '+964 770 234 5678' },
  { id: 's3', name: 'Chrani Service Center - Sulaymaniyah', address: 'Salim Street, Opposite City Mall, Sulaymaniyah, Kurdistan Region', phone: '+964 770 345 6789' },
];

export const userManuals = [
  { id: 'm1', title: 'Refrigerator User Guide', size: '2.4 MB', category: 'Refrigerators' },
  { id: 'm2', title: 'Washing Machine Manual', size: '1.8 MB', category: 'Washing Machines' },
  { id: 'm3', title: 'Air Conditioner Installation Guide', size: '3.1 MB', category: 'Air Conditioners' },
  { id: 'm4', title: 'Oven Safety & Usage Manual', size: '1.5 MB', category: 'Ovens & Cookers' },
];

export const videoTutorials = [
  { id: 'v1', title: 'How to Install Your AC Unit', youtubeId: 'dQw4w9WgXcQ' },
  { id: 'v2', title: 'Washing Machine Maintenance Tips', youtubeId: 'dQw4w9WgXcQ' },
  { id: 'v3', title: 'Refrigerator Temperature Settings Guide', youtubeId: 'dQw4w9WgXcQ' },
];
