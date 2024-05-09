<?php

namespace LlmLaraHub\TagFunction\Tests\Feature;

use App\Models\DocumentChunk;
use LlmLaraHub\LlmDriver\LlmDriverFacade;
use LlmLaraHub\LlmDriver\Responses\CompletionResponse;
use LlmLaraHub\TagFunction\TagManager;
use Tests\TestCase;

class TagManagerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_talks_to_llm(): void
    {
        $tags = get_fixture('taggings_results_from_llm.json');
        LlmDriverFacade::shouldReceive('driver->completion')->times(2)->andReturn(
            CompletionResponse::from([
                'content' => $tags,
            ])
        );
        $content = <<<'EOT'
        61Accelerate: State of DevOps 2019   |    How Do We Improve Productivity?    
        We wondered if the amount of juggling work would be 
        significantly different among our highest and lowest 
        performers—after all, productivity is the ability to get 
        work done and feel like you are in “a flow.” 
        To capture this, we asked respondents a few questions: 
        • How many roles they juggle or different types of 
        work do they do regardless of their official job title  
        • How many projects they switched between in a day
        • How many projects they were working on overall
         
        Surprisingly, we did not detect significant differences 
        between low, medium, high, and elite performers. 
        Therefore, we cannot conclude that how well teams 
        develop and deliver software affects the number of 
        roles and projects that respondents juggle. There is  
        no such thing as “push through this phase and it will 
        get significantly better.” Instead, we should take steps 
        to make our work sustainable. That is done through 
        process improvement work and automation, which  
        will reduce toil and make the work repeatable, 
        consistent, fast, scalable, and auditable. It will  
        also free us up to do new, creative work.
        PRODUCTIVITY,  
        BURNOUT, AND  
        JUGGLING WORK
        Technical professionals and tools 
        Our work in 2017 found that empowered teams 
        who make their own decisions about tools and 
        implementations contribute to better software 
        delivery performance. In this year’s research, we 
        see that given the opportunity, high performers 
        choose useful and usable tools, and these kinds 
        of tools improve productivity.
        This has important implications for product 
        design. Products that have both utility and 
        usability are more likely to be adopted by 
        technology professionals, and when they are 
        used, have better outcomes. These kinds of 
        tools should be prioritized by industry leaders. 
        It's not enough to deliver products that are 
        feature complete; they also need to be usable 
        to be adopted and deliver value during a 
        DevOps transformation. 
EOT;
        $documentChunk = DocumentChunk::factory()->create([
            'content' => $content,
        ]);

        //$this->fakeVerify($documentChunk->document, 4, 'Tag Example, Tag Example other Test, Tag Example Test');

        (new TagManager())->handle($documentChunk->document);

        $this->assertCount(10, $documentChunk->document->refresh()->tags);

        (new TagManager())->handle($documentChunk->document);

        $this->assertCount(10, $documentChunk->document->refresh()->tags);
    }
}
