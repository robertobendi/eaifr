import { HomeHero } from '@/components/page-sections/HomeHero';
import { HomeVision } from '@/components/page-sections/HomeVision';
import { HomeResearchPreview } from '@/components/page-sections/HomeResearchPreview';
import { HomeEducation } from '@/components/page-sections/HomeEducation';
import { HomeGlobalReach } from '@/components/page-sections/HomeGlobalReach';
import { HomeEventsAndNews } from '@/components/page-sections/HomeEventsPreview';
import { HomePartners } from '@/components/page-sections/HomePartners';

export default function HomePage() {
  return (
    <>
      <HomeHero />
      <HomeVision />
      <HomeResearchPreview />
      <HomeEducation />
      <HomeGlobalReach />
      <HomeEventsAndNews />
      <HomePartners />
    </>
  );
}
