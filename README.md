PHP Romanian stemmer class
==========================

The implementation was done according to Romanian stemmer algorithm in Snowball.  If you find issue, please send an email to claudiu@claudiupersoiu.ro.

Stemming = In linguistic morphology and information retrieval, stemming is the process for reducing inflected (or sometimes derived) words to their stem, base or root form—generally a written word form. The stem need not be identical to the morphological root of the word; it is usually sufficient that related words map to the same stem, even if this stem is not in itself a valid root. (http://en.wikipedia.org/wiki/Stemming)

PHP5 Implementation of the Snowball Romanian stemming algorithm (http://snowball.tartarus.org/algorithms/romanian/stemmer.html).

The stemmer should work bouth with or without diacritics.

USAGE
=====
    $stem = RomanianStemmer::Stem($word);

Project URL
===========
http://blog.claudiupersoiu.ro/php-romanian-stemmer-class/

NOTE: You must open this document as a UTF-8 file, or you'll override the diacritics.

DISCLAIMER

 IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.